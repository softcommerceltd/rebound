<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Model;

use Magento\Catalog\Helper\ImageFactory as ProductHelperImageFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\Order\Item as SalesOrderItem;
use SoftCommerce\Core\Model\Source\Status;
use SoftCommerce\Rebound\Api;
use SoftCommerce\Rebound\Helper\Data as Helper;
use SoftCommerce\Rebound\Http\ClientInterface;
use SoftCommerce\Rebound\Http\OrderInterface as ClientOrderInterface;
use SoftCommerce\Rebound\Logger\Logger;

/**
 * Class OrderExportManagement
 * @package SoftCommerce\Rebound\Model
 * @deprecared
 */
class OrderExportManagement extends OrderExportAbstractManagement implements Api\OrderExportManagementInterface
{
    /**
     * @var ClientInterface
     */
    private ClientInterface $client;

    /**
     * @var ProductHelperImageFactory
     */
    private ProductHelperImageFactory $productHelperImageFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $salesOrderRepository;

    /**
     * @var SalesOrder
     */
    private SalesOrder $salesOrder;

    /**
     * @var SearchCriteriaInterface
     */
    private SearchCriteriaInterface $searchCriteria;

    /**
     * @var array
     */
    private array $clientEntries = [];

    /**
     * @var array
     */
    private array $clientOrder = [];

    /**
     * @var array|null
     */
    private ?array $clientResponse = [];

    /**
     * @var array
     */
    private array $targetIds = [];

    /**
     * OrderExportManagement constructor.
     * @param ClientInterface $client
     * @param OrderRepositoryInterface $orderRepository
     * @param Api\OrderExportRepositoryInterface $orderExportRepository
     * @param ProductHelperImageFactory $productHelperImageFactory
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Helper $helper
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param Json $serializer
     */
    public function __construct(
        ClientInterface $client,
        OrderRepositoryInterface $orderRepository,
        Api\OrderExportRepositoryInterface $orderExportRepository,
        ProductHelperImageFactory $productHelperImageFactory,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Helper $helper,
        DateTime $dateTime,
        Logger $logger,
        Json $serializer
    ) {
        $this->client = $client;
        $this->salesOrderRepository = $orderRepository;
        $this->productHelperImageFactory = $productHelperImageFactory;
        parent::__construct($orderExportRepository, $filterBuilder, $filterGroupBuilder, $searchCriteriaBuilder, $helper, $dateTime, $logger, $serializer);
    }

    /**
     * @return SearchCriteriaInterface
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria)
    {
        $this->searchCriteria = $searchCriteria;
        return $this;
    }

    /**
     * @param bool $keys
     * @return array
     * @deprecared
     */
    public function getTargetIds($keys = false)
    {
        return false === $keys
            ? $this->targetIds
            : array_keys($this->targetIds ?: []);
    }

    /**
     * @param array $targetIds
     * @return $this
     * @deprecared
     */
    public function setTargetIds(array $targetIds)
    {
        $this->targetIds = $targetIds;
        return $this;
    }

    /**
     * @return SalesOrder
     * @throws LocalizedException
     */
    public function getSalesOrder()
    {
        if (null === $this->salesOrder) {
            throw new LocalizedException(__('Order is not set.'));
        }

        return $this->salesOrder;
    }

    /**
     * @return $this|OrderExportManagement
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->executeBefore();

        $collection = $this->getSalesOrderCollection();
        if (!$collection->getTotalCount()) {
            $this->addResponse('Orders are up-to-date.', Status::COMPLETE);
            return $this;
        }

        try {
            $this->processMultipleBefore()
                ->processMultiple($collection->getItems())
                ->processMultipleAfter();
        } catch (CouldNotSaveException $e) {
            $this->setErrors($e->getMessage());
            $this->logger->log(100, $e->getMessage(), [__METHOD__]);
        } catch (\Exception $e) {
            $this->setErrors($e->getMessage());
            $this->logger->log(100, $e->getMessage(), [__METHOD__]);
        }

        $this->executeAfter();

        return $this;
    }

    /**
     * @return $this
     */
    private function executeBefore()
    {
        $this->error =
        $this->request =
        $this->response =
            [];

        return $this;
    }

    /**
     * @return $this
     */
    private function executeAfter()
    {
        if (!$this->helper->getIsActiveDebug()) {
            return $this;
        }

        $context = [];

        if ($request = $this->getRequest()) {
            $context['request'][] = $request;
        }

        if ($response = $this->getResponse()) {
            $context['response'][] = $response;
        }

        if (empty($context)) {
            return $this;
        }

        if ($this->helper->getIsDebugPrintToArray()) {
            $this->logger->debug(print_r([__METHOD__ => $context], true), []);
            if (!empty($this->error)) {
                $this->logger->debug(print_r([__METHOD__ => $this->error], true), []);
            }
        } else {
            $this->logger->debug(__METHOD__, $context);
            if (!empty($this->error)) {
                $this->logger->debug(__METHOD__, $this->error);
            }
        }

        return $this;
    }

    /**
     * @return OrderSearchResultInterface
     * @throws LocalizedException
     */
    private function getSalesOrderCollection()
    {
        if ($searchCriteria = $this->getSearchCriteria()) {
            return $this->salesOrderRepository->getList($searchCriteria);
        }

        $date = $this->dateTime->gmtDate(null, strtotime('-12 months'));

        $filter = $this->filterBuilder
            ->setField(OrderInterface::CREATED_AT)
            ->setValue($date)
            ->setConditionType('gteq')
            ->create();
        $group[] = $this->filterGroupBuilder->setFilters([$filter])->create();

        $filter = $this->filterBuilder
            ->setField(OrderInterface::STATUS)
            ->setValue([Status::COMPLETE, 'pap'])
            ->setConditionType('in')
            ->create();
        $group[] = $this->filterGroupBuilder->setFilters([$filter])->create();

        if ($processedEntries = $this->orderExportRepository
            ->getProcessedEntries(Api\Data\OrderExportInterface::ENTITY_ID)) {
            $processedEntries = array_column($processedEntries, Api\Data\OrderExportInterface::ENTITY_ID);
            $filter = $this->filterBuilder
                ->setField(OrderInterface::ENTITY_ID)
                ->setValue($processedEntries)
                ->setConditionType('nin')
                ->create();
            $group[] = $this->filterGroupBuilder->setFilters([$filter])->create();
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->setFilterGroups($group)
            ->setPageSize(self::PROCESS_SIZE)
            ->create();

        return $this->salesOrderRepository->getList($searchCriteria);
    }

    /**
     * @return $this
     */
    private function processMultipleBefore()
    {
        $this->clientEntries = [];
        return $this;
    }

    /**
     * @param array $orders
     * @return $this
     * @throws LocalizedException
     */
    private function processMultiple(array $orders)
    {
        /** @var SalesOrder $order */
        foreach ($orders as $order) {
            try {
                $this->processBefore($order)
                    ->process()
                    ->processAfter();
            } catch (\Exception $e) {
                $this->setClientEntry(Status::ERROR, $e->getMessage())
                    ->addResponse($e->getMessage(), Status::ERROR);
            }
        }

        return $this;
    }

    /**
     * @return $this
     * @throws CouldNotSaveException
     */
    private function processMultipleAfter()
    {
        if (empty($this->clientEntries)) {
            return $this;
        }

        $this->orderExportRepository->saveMultiple($this->clientEntries);
        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function process()
    {
        $this->generateRequest()
            ->submit();

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
        $this->clientResponse =
            [];

        $this->salesOrder = $order;
        $orderId = $this->getSalesOrder()->getId();

        $this->clientEntries[$orderId] = [
            Api\Data\OrderExportInterface::ENTITY_ID => $orderId,
            Api\Data\OrderExportInterface::INCREMENT_ID => $this->getSalesOrder()->getIncrementId(),
            Api\Data\OrderExportInterface::EXTERNAL_ID => $this->getClientOrder()
        ];

        if (!$this->getClientOrder()) {
            $this->clientEntries[$orderId][Api\Data\OrderExportInterface::CREATED_AT] = $this->dateTime->gmtDate();
        }

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    private function processAfter()
    {
        $salesOrder = $this->getSalesOrder();

        if (empty($this->clientResponse)) {
            throw new LocalizedException(
                __('Could not retrieve order response data. [Order: %1]', $salesOrder->getIncrementId())
            );
        }

        $status = key($this->clientResponse) === Status::ERROR
            ? Status::ERROR
            : Status::COMPLETE;

        $response = current($this->clientResponse);

        $externalId = $response[ClientOrderInterface::ORDER_ID] ?? null;
        if (null !== $externalId) {
            $this->setClientOrder($externalId);
        }

        $message = $response[ClientOrderInterface::MESSAGE]
            ?? ($status === Status::ERROR
                ? __('Could not create order. [Order: %1]', $salesOrder->getIncrementId())
                : __('Order has been created. [Order: %1, External ID: %2', $salesOrder->getIncrementId(), $externalId));

        $this->setClientEntry($status, $message)
            ->addResponse(
                [
                    $salesOrder->getIncrementId() => $externalId
                ]
            );

        if ($status === Status::ERROR) {
            $this->error[] = $response;
        }

        return $this;
    }

    /**
     * @return string|int|null
     * @throws LocalizedException
     */
    private function getClientOrder()
    {
        if (!isset($this->clientOrder[$this->getSalesOrder()->getId()])) {
            $this->setClientOrder(
                $this->orderExportRepository->getClientOrderId($this->getSalesOrder()->getId())
            );
        }

        return $this->clientOrder[$this->getSalesOrder()->getId()];
    }

    /**
     * @param int|string|null|false $clientOrderId
     * @return $this
     * @throws LocalizedException
     */
    private function setClientOrder($clientOrderId)
    {
        $this->clientOrder[$this->getSalesOrder()->getId()] = $clientOrderId ?: false;
        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    private function generateRequest()
    {
        $this->buildOrderData()
            ->buildItemData();

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    private function buildOrderData()
    {
        $order = $this->getSalesOrder();
        if (!$address = $order->getShippingAddress()) {
            if (!$address = $order->getBillingAddress()) {
                throw new LocalizedException(__('Could not get order address. Order: %1', $order->getIncrementId()));
            }
        }

        $this->setRequest(
            [
                ClientOrderInterface::ORDER_REFERENCE => $order->getIncrementId(),
                ClientOrderInterface::OVERWRITE_DATA => (bool) $this->getClientOrder(),
                ClientOrderInterface::ORDER_DATE => $order->getCreatedAt(),
                ClientOrderInterface::CONTACT_NAME => $order->getCustomerName(),
                ClientOrderInterface::COMPANY_NAME => $address->getCompany(),
                ClientOrderInterface::ADDR1 => $address->getStreetLine(1),
                ClientOrderInterface::ADDR2 => $address->getStreetLine(2),
                ClientOrderInterface::ADDR3 => $address->getStreetLine(3),
                ClientOrderInterface::CITY => $address->getCity(),
                ClientOrderInterface::ZIP => $address->getPostcode(),
                ClientOrderInterface::STATE => $address->getRegionCode(),
                ClientOrderInterface::COUNTRY => $address->getCountryId(),
                ClientOrderInterface::PHONE => $address->getTelephone(),
                ClientOrderInterface::EMAIL => $order->getCustomerEmail(),
                ClientOrderInterface::CURRENCY_CODE => $order->getOrderCurrencyCode(),
                ClientOrderInterface::EXPORT_AWB => $order->getAwb(),
                ClientOrderInterface::EXPORT_CARRIER_NAME => $order->getShippingService(),
                ClientOrderInterface::FREE_RETURN => $order->getIsFreeReturn(),
                ClientOrderInterface::CUSTOM_FIELDS => [
                    ClientOrderInterface::FIELD1 => $order->getId(),
                    ClientOrderInterface::FIELD2 => $order->getStoreId(),
                    ClientOrderInterface::FIELD3 => $order->getShippingDescription(),
                    ClientOrderInterface::FIELD4 => $order->getShippingAmount(),
                    ClientOrderInterface::FIELD5 => $order->getWeight()
                ],
            ]
        );

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    private function buildItemData()
    {
        $order = $this->getSalesOrder();
        if (!$address = $order->getShippingAddress()) {
            if (!$address = $order->getBillingAddress()) {
                throw new LocalizedException(__('Could not get order address. Order: %1', $order->getIncrementId()));
            }
        }

        $request = [];
        /** @var SalesOrderItem $item */
        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $request[] = [
                ClientOrderInterface::SKU_CODE => $item->getSku(),
                ClientOrderInterface::SKU_DESC => $item->getName(),
                ClientOrderInterface::QUANTITY => $item->getQtyOrdered(),
                ClientOrderInterface::PRICE => $item->getPriceInclTax(),
                ClientOrderInterface::WEIGHT => ($item->getWeight() / 1000),
                ClientOrderInterface::LENGTH => $item->getLength(),
                ClientOrderInterface::WIDTH => $item->getWidth(),
                ClientOrderInterface::HEIGHT => $item->getHeight(),
                ClientOrderInterface::DIMENSIONS_UOM => 'mm',
                ClientOrderInterface::HS_CODE => null,
                ClientOrderInterface::COUNTRY_CODE => $address->getCountryId(),
                ClientOrderInterface::DANGEROUS_GOODS => 'No',
                ClientOrderInterface::EXPORT_DATE => $this->dateTime->gmtDate(),
                ClientOrderInterface::EXPORT_AWB => $order->getAwb(),
                ClientOrderInterface::TRACKING => $order->getTrackingNumbers(),
                ClientOrderInterface::DAYS_FOR_RETURN => 365,
                ClientOrderInterface::SKU_URL => null !== $product ? $product->getProductUrl() : '',
                ClientOrderInterface::IMG_PATH => null !== $product
                    ? (string) $this->productHelperImageFactory
                        ->create()
                        ->init($product, 'product_page_image_large')
                        ->setImageFile($product->getImage())
                        ->getUrl()
                    : '',
                ClientOrderInterface::CUSTOM_FIELDS => []
            ];
        }

        $this->setRequest($request, 'item');

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function submit()
    {
        if (empty($this->getRequest())) {
            return $this;
        }

        $this->client->execute(
            $this->helper->getClientOrderCreateUrl(),
            ['order' => $this->getRequest()]
        );

        $this->clientResponse = $this->client->getResponseContents();

        return $this;
    }

    /**
     * @param $status
     * @param $message
     * @return $this
     * @throws LocalizedException
     */
    private function setClientEntry($status, $message)
    {
        $this->clientEntries[$this->getSalesOrder()->getId()] = [
            Api\Data\OrderExportInterface::ENTITY_ID => $this->getSalesOrder()->getId(),
            Api\Data\OrderExportInterface::INCREMENT_ID => $this->getSalesOrder()->getIncrementId(),
            Api\Data\OrderExportInterface::EXTERNAL_ID => $this->getClientOrder() ?: null,
            Api\Data\OrderExportInterface::STATUS => $status,
            Api\Data\OrderExportInterface::MESSAGE => $message,
            Api\Data\OrderExportInterface::REQUEST_ENTRY => $this->serializer->serialize($this->getRequest()),
            Api\Data\OrderExportInterface::RESPONSE_ENTRY => $this->serializer
                ->serialize($this->clientResponse ?: []),
            Api\Data\OrderExportInterface::UPDATED_AT => $this->dateTime->gmtDate()
        ];

        return $this;
    }
}
