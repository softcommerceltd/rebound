<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Rebound\Service;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use SoftCommerce\Rebound\Api;
use SoftCommerce\Rebound\Api\OrderExportRepositoryInterface;
use SoftCommerce\Rebound\Logger\Logger;
use SoftCommerce\Rebound\Model\ConfigInterface;
use SoftCommerce\Rebound\Model\ResourceModel;
use SoftCommerce\Rebound\Model\Source\Status;

/**
 * Class OrderExport
 * @package SoftCommerce\Rebound\Service
 */
class OrderExport extends OrderExport\AbstractService implements OrderExportInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $salesOrderRepository;

    /**
     * @var OrderExportRepositoryInterface
     */
    private OrderExportRepositoryInterface $orderExportRepository;

    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private OrderStatusHistoryRepositoryInterface $salesOrderHistoryRepository;

    /**
     * @var HistoryFactory
     */
    private HistoryFactory $salesOrderHistoryFactory;

    /**
     * @var ResourceModel\OrderExport
     */
    private ResourceModel\OrderExport $resource;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private FilterGroupBuilder $filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private FilterBuilder $filterBuilder;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @var OrderExport\ProcessorInterface[]
     */
    private array $processors;

    /**
     * OrderExport constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderExportRepositoryInterface $orderExportRepository
     * @param OrderStatusHistoryRepositoryInterface $salesOrderHistoryRepository
     * @param HistoryFactory $salesOrderHistoryFactory
     * @param ResourceModel\OrderExport $resource
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param FilterBuilder $filterBuilder
     * @param ConfigInterface $config
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param Json $serializer
     * @param array $data
     * @param array $processors
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderExportRepositoryInterface $orderExportRepository,
        OrderStatusHistoryRepositoryInterface $salesOrderHistoryRepository,
        HistoryFactory $salesOrderHistoryFactory,
        ResourceModel\OrderExport $resource,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        ConfigInterface $config,
        DateTime $dateTime,
        Logger $logger,
        Json $serializer,
        array $data = [],
        array $processors = []
    ) {
        $this->salesOrderRepository = $orderRepository;
        $this->orderExportRepository = $orderExportRepository;
        $this->salesOrderHistoryRepository = $salesOrderHistoryRepository;
        $this->salesOrderHistoryFactory = $salesOrderHistoryFactory;
        $this->resource = $resource;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->config = $config;
        $this->processors = $processors;
        $this->initialise($data);
        parent::__construct($dateTime, $logger, $serializer);
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->executeBefore();

        if (!$searchCriteria = $this->getSearchCriteria()) {
            $filter = $this->filterBuilder
                ->setField(Api\Data\OrderExportInterface::REBOUND_ORDER_STATUS)
                ->setValue(Status::PENDING)
                ->setConditionType('eq')
                ->create();
            $filterGroup[] = $this->filterGroupBuilder->setFilters([$filter])->create();

            $filter = $this->filterBuilder
                ->setField(Api\Data\OrderExportInterface::STATUS)
                ->setValue([Status::COMPLETE, Status::PROCESSING, Status::PAP])
                ->setConditionType('in')
                ->create();
            $filterGroup[] = $this->filterGroupBuilder->setFilters([$filter])->create();

            $searchCriteria = $this->searchCriteriaBuilder
                ->setFilterGroups($filterGroup)
                ->create();
        }

        $searchCriteria->setPageSize($this->getProcessSize() ?: self::PROCESS_SIZE);
        $collection = $this->salesOrderRepository->getList($searchCriteria);
        if (!$collection->getTotalCount()) {
            $this->addResponse('Orders are up-to-date.', Status::COMPLETE)
                ->executeAfter();
            return $this;
        }

        /** @var SalesOrder $order */
        foreach ($collection->getItems() as $order) {
            if (!$this->canProcess($order)) {
                continue;
            }
            
            try {
                $this->process($order);
            } catch (\Exception $e) {
                $this->handleError($e->getMessage())
                    ->addResponse([$order->getId() => $e->getMessage()], Status::ERROR);
            }
        }

        $this->executeAfter();
        return $this;
    }

    /**
     * @return $this
     */
    protected function executeAfter()
    {
        if (!$this->config->getIsActiveDebug()) {
            return $this;
        }

        $context = [];
        if ($request = $this->getRequest()) {
            $context['request'][] = $request;
        }

        if ($response = $this->getResponse()) {
            $context['response'][] = $response;
        }

        if ($error = $this->getError()) {
            $context['error'][] = $error;
        }

        if (empty($context)) {
            return $this;
        }

        if ($this->config->getIsDebugPrintToArray()) {
            $this->logger->debug(print_r([__METHOD__ => $context], true), []);
        } else {
            $this->logger->debug(__METHOD__, $context);
        }

        return $this;
    }

    /**
     * @param SalesOrder $order
     * @return $this
     * @throws LocalizedException
     */
    private function processBefore(SalesOrder $order)
    {
        $this->error =
        $this->request =
            [];
        $this->salesOrder = $order;
        $orderId = $this->getSalesOrder()->getId();

        if (!isset($this->clientOrder[$orderId])) {
            foreach ($this->resource->getClientOrders((int) $orderId, $this->getEntityFilter()) as $item) {
                if (!isset($item[Api\Data\OrderExportInterface::ENTITY_TYPE])) {
                    continue;
                }
                $this->addToClientOrder(
                    $item[Api\Data\OrderExportInterface::ENTITY_TYPE],
                    [
                        Api\Data\OrderExportInterface::EXTERNAL_ID => $item[Api\Data\OrderExportInterface::EXTERNAL_ID]
                            ?? null
                    ]
                );
            }
        }

        return $this;
    }

    /**
     * @param SalesOrder $order
     * @return $this
     * @throws LocalizedException
     */
    private function process(SalesOrder $order)
    {
        $this->processBefore($order);
        foreach ($this->processors as $entityType => $processor) {
            $processor->execute();
        }
        $this->processAfter();
        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    private function processAfter()
    {
        if (!$clientOrders = $this->getClientOrder()) {
            return $this;
        }

        $bind =
        $status =
        $externalId =
            [];
        $salesOrder = $this->getSalesOrder();
        $commentHtml = '<b>' . __('Rebound Order Synchronisation.') . '</b><br />';
        foreach ($this->getClientOrder() as $entity => $order) {
            if (isset($order[Api\Data\OrderExportInterface::MESSAGE])) {
                $message = __(
                    '%1. [Order: %2, Client ID: %3, Entity: %4]',
                    $order[Api\Data\OrderExportInterface::MESSAGE],
                    $salesOrder->getIncrementId(),
                    $order[Api\Data\OrderExportInterface::EXTERNAL_ID] ?? null,
                    $entity
                );
                $commentHtml .= "<i>$message</i><br />";
            }

            if (isset($order[Api\Data\OrderExportInterface::EXTERNAL_ID])) {
                $externalId[$entity] = $order[Api\Data\OrderExportInterface::EXTERNAL_ID];
            }

            if (isset($order[Api\Data\OrderExportInterface::STATUS])) {
                $status[] = $order[Api\Data\OrderExportInterface::STATUS];
            }
        }

        if (!empty($externalId)) {
            $externalId = $this->serializer->serialize($externalId);
            $bind[Api\Data\OrderExportInterface::REBOUND_ORDER] = $externalId;
        }

        if ((in_array(Status::ERROR, $status) && in_array(Status::COMPLETE, $status))
            || in_array(Status::PROCESSING, $status)
        ) {
            $status = Status::WARNING;
        } elseif (in_array(Status::ERROR, $status)) {
            $status = Status::ERROR;
        } else {
            $status = Status::COMPLETE;
        }

        $bind[Api\Data\OrderExportInterface::REBOUND_ORDER_STATUS] = $status;

        try {
            $this->resource->insertOnDuplicate($clientOrders);
            $history = $this->salesOrderHistoryFactory
                ->create()
                ->setParentId($salesOrder->getId())
                ->setStatus($salesOrder->getStatus() ?: Status::PROCESSING)
                ->setComment($commentHtml)
                ->setEntityName('order')
                ->setIsCustomerNotified(false)
                ->setIsVisibleOnFront(false);
            $this->salesOrderHistoryRepository->save($history);

            if (empty($bind)) {
                return $this;
            }

            $adapter = $this->resource->getConnection();
            $adapter->update(
                $adapter->getTableName('sales_order'),
                $bind,
                [OrderInterface::ENTITY_ID . ' = ?' => $salesOrder->getId()]
            );
        } catch (\Exception $e) {
            throw new LocalizedException(__(
                'Could not update sales order. [Order: %1, Reason: %2]',
                $salesOrder->getIncrementId(),
                $e->getMessage()
            ));
        }

        return $this;
    }

    /**
     * @param $message
     * @return $this
     * @throws LocalizedException
     */
    private function handleError($message)
    {
        foreach ($this->getClientOrder() as $entity => $order) {
            $this->addToClientOrder(
                $entity,
                [
                    Api\Data\OrderExportInterface::STATUS => Status::ERROR,
                    Api\Data\OrderExportInterface::MESSAGE => $message
                ]
            );
        }

        return $this->processAfter();
    }

    /**
     * @param array $data
     */
    private function initialise(array $data)
    {
        if (isset($data[self::ENTITY_FILTER])) {
            $this->setEntityFilter(
                is_array($data[self::ENTITY_FILTER])
                    ? $data[self::ENTITY_FILTER]
                    : [$data[self::ENTITY_FILTER]]
            );
        }

        if (isset($data[self::SEARCH_CRITERIA])) {
            $this->setSearchCriteria($data[self::SEARCH_CRITERIA]);
        }

        foreach ($this->processors as $processor) {
            $processor->init($this);
        }
    }

    /**
     * @param SalesOrder $salesOrder
     * @return bool
     */
    protected function canProcess(SalesOrder $salesOrder): bool
    {
        return (bool) $salesOrder->getShipmentsCollection()->getSize();
    }
}
