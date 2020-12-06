<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Rebound\Service\OrderExport;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order as SalesOrder;
use SoftCommerce\Rebound\Api;
use SoftCommerce\Rebound\Logger\Logger;
use SoftCommerce\Rebound\Model\Source\Status;
use SoftCommerce\Rebound\Service\OrderExport;

/**
 * Class AbstractService
 * @package SoftCommerce\Rebound\Service\OrderExport
 */
class AbstractService
{
    /**
     * @var DateTime
     */
    protected DateTime $dateTime;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var Json
     */
    protected Json $serializer;

    /**
     * @var OrderExport|null
     */
    protected ?OrderExport $context = null;

    /**
     * @var SalesOrder|null
     */
    protected ?SalesOrder $salesOrder = null;

    /**
     * @var SearchCriteriaInterface|null
     */
    protected ?SearchCriteriaInterface $searchCriteria = null;

    /**
     * @var string|null
     */
    protected ?string $behaviour = null;

    /**
     * @var array
     */
    protected array $error = [];

    /**
     * @var array
     */
    protected array $request = [];

    /**
     * @var array
     */
    protected array $response = [];

    /**
     * @var array
     */
    protected array $entityFilter = [];

    /**
     * @var array
     */
    protected array $clientOrder = [];

    /**
     * @var array|null
     */
    protected ?array $clientResponse = [];

    /**
     * @var array|null
     */
    protected array $salesOrderReboundId = [];

    /**
     * @var int|null
     */
    private ?int $processSize = null;

    /**
     * AbstractService constructor.
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param Json $serializer
     */
    public function __construct(
        DateTime $dateTime,
        Logger $logger,
        Json $serializer
    ) {
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    public function getError($key = null)
    {
        return $key
            ? ($this->error[$key] ?? [])
            : ($this->error ?: []);
    }

    /**
     * @param int|string|array $data
     * @param int|string|null $key
     * @return $this
     */
    public function setError($data, $key = null)
    {
        null !== $key
            ? $this->error[$key][] = $data
            : $this->error[] = $data;
        return $this;
    }

    /**
     * @param null $key
     * @return array|null
     */
    public function getResponse($key = null)
    {
        return null !== $key
            ? ($this->response[$key] ?? [])
            : ($this->response ?: []);
    }

    /**
     * @param int|string|array $data
     * @param int|string|null $key
     * @return $this
     */
    public function setResponse($data, $key = null)
    {
        null !== $key
            ? $this->response[$key] = $data
            : $this->response = $data;
        return $this;
    }

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    public function addResponse($data, $key = null)
    {
        null !== $key
            ? $this->response[$key][] = $data
            : $this->response[] = $data;
        return $this;
    }

    /**
     * @param int|string|null $key
     * @return array|string|mixed
     */
    public function getRequest($key = null)
    {
        return null !== $key
            ? ($this->request[$key] ?? [])
            : ($this->request ?: []);
    }

    /**
     * @param $value
     * @param null $key
     * @return $this
     */
    public function setRequest($value, $key = null)
    {
        null !== $key
            ? $this->request[$key] = $value
            : $this->request = $value;
        return $this;
    }

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    public function addRequest($data, $key = null)
    {
        null !== $key
            ? $this->request[$key][] = $data
            : $this->request[] = $data;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBehaviour(): ?string
    {
        return $this->behaviour;
    }

    /**
     * @param string $behaviour
     * @return $this
     */
    public function setBehaviour(string $behaviour)
    {
        $this->behaviour = $behaviour;
        return $this;
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
     * @return array
     */
    public function getEntityFilter(): array
    {
        return $this->entityFilter ?: [];
    }

    /**
     * @param array $entityType
     * @return $this
     */
    public function setEntityFilter(array $entityType)
    {
        $this->entityFilter = $entityType;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getProcessSize(): ?int
    {
        return $this->processSize;
    }

    /**
     * @param int $processSize
     * @return $this
     */
    public function setProcessSize(int $processSize)
    {
        $this->processSize = $processSize;
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
     * @return string|null
     * @throws LocalizedException
     */
    public function getSalesOrderReboundStatus(): ?string
    {
        return $this->getSalesOrder()->getData(Api\Data\OrderExportInterface::REBOUND_ORDER_STATUS);
    }

    /**
     * @param string $entityType
     * @return int|string|null
     * @throws LocalizedException
     */
    public function getSalesOrderReboundId(string $entityType)
    {
        $salesOrder = $this->getSalesOrder();
        if (isset($this->salesOrderReboundId[$entityType])) {
            return $this->salesOrderReboundId[$entityType];
        }

        if (!$clientId = $salesOrder->getData(Api\Data\OrderExportInterface::REBOUND_ORDER)) {
            return null;
        }

        try {
            $clientId = $this->serializer->unserialize($clientId);
            $this->salesOrderReboundId = (array) $clientId;
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        return $this->salesOrderReboundId[$entityType] ?? null;
    }

    /**
     * @param string|null $entityType
     * @param string|null $metadata
     * @return array|mixed|null
     */
    public function getClientOrder(?string $entityType = null, ?string $metadata = null)
    {
        return null !== $entityType
            ? (null === $metadata
                ? ($this->clientOrder[$entityType] ?? [])
                : ($this->clientOrder[$entityType][$metadata] ?? null))
            : ($this->clientOrder ?: []);
    }

    /**
     * @param string $entityType
     * @param array $data
     * @return $this
     * @throws LocalizedException
     */
    public function addToClientOrder(string $entityType, array $data = [])
    {
        if (!$clientOrder = $this->getClientOrder($entityType)) {
            $clientOrder = [
                Api\Data\OrderExportInterface::ENTITY_TYPE => $entityType,
                Api\Data\OrderExportInterface::ORDER_ID => $this->getSalesOrder()->getId(),
                Api\Data\OrderExportInterface::INCREMENT_ID => $this->getSalesOrder()->getIncrementId(),
                Api\Data\OrderExportInterface::EXTERNAL_ID => null,
                Api\Data\OrderExportInterface::REFERENCE_ID => $this->getSalesOrder()->getIncrementId(),
                Api\Data\OrderExportInterface::STATUS => Status::PROCESSING,
                Api\Data\OrderExportInterface::MESSAGE => 'Order being exported.',
                Api\Data\OrderExportInterface::RESPONSE_ENTRY => null,
                Api\Data\OrderExportInterface::CREATED_AT => $this->dateTime->gmtDate(),
                Api\Data\OrderExportInterface::UPDATED_AT => $this->dateTime->gmtDate()
            ];
        }

        $clientOrder = array_merge($clientOrder, $data);
        $this->clientOrder[$entityType] = $clientOrder;

        return $this;
    }

    /**
     * @param $needle
     * @param array $haystack
     * @param $columnName
     * @param null $columnId
     * @return false|int|string
     */
    public function getSearchArrayMatch(
        $needle,
        array $haystack,
        $columnName,
        $columnId = null
    ) {
        return array_search($needle, array_column($haystack, $columnName, $columnId));
    }

    /**
     * @param $context
     * @return $this
     */
    public function init(OrderExport $context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return OrderExport|null
     */
    public function getContext(): OrderExport
    {
        return $this->context;
    }

    /**
     * @return $this
     */
    protected function executeBefore()
    {
        $this->error =
        $this->request =
        $this->response =
            [];
        return $this;
    }

    protected function executeAfter()
    {
        return $this;
    }
}
