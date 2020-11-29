<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

use SoftCommerce\Rebound\Api;
use SoftCommerce\Rebound\Model\Source\OrderEntityType;

/**
 * Class OrderExportRepository
 * @package SoftCommerce\Rebound\Model
 */
class OrderExportRepository implements Api\OrderExportRepositoryInterface
{
    /**
     * @var ResourceModel\OrderExport
     */
    private $_resource;

    /**
     * @var OrderExportFactory
     */
    private $_entityFactory;

    /**
     * @var ResourceModel\OrderExport\CollectionFactory
     */
    private $_collectionFactory;

    /**
     * @var Api\Data\OrderExportSearchResultsInterface
     */
    private $_searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $_collectionProcessor;

    /**
     * OrderExportRepository constructor.
     * @param ResourceModel\OrderExport $resource
     * @param OrderExportFactory $entityFactory
     * @param ResourceModel\OrderExport\CollectionFactory $collectionFactory
     * @param Api\Data\OrderExportSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceModel\OrderExport $resource,
        OrderExportFactory $entityFactory,
        ResourceModel\OrderExport\CollectionFactory $collectionFactory,
        Api\Data\OrderExportSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->_resource = $resource;
        $this->_entityFactory = $entityFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_searchResultsFactory = $searchResultsFactory;
        $this->_collectionProcessor = $collectionProcessor
            ?: ObjectManager::getInstance()->get(CollectionProcessorInterface::class);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Api\Data\OrderExportSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ResourceModel\OrderExport\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $this->_collectionProcessor->process($searchCriteria, $collection);

        /** @var Api\Data\OrderExportSearchResultsInterface $searchResults */
        $searchResult = $this->_searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return array
     */
    public function getAllIds(SearchCriteriaInterface $searchCriteria = null)
    {
        /** @var ResourceModel\OrderExport\Collection $collection */
        $collection = $this->_collectionFactory->create();
        if (null === $searchCriteria) {
            return $collection->getAllIds();
        }

        $this->_collectionProcessor->process($searchCriteria, $collection);

        return $collection->getAllIds();
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getPendingRecords()
    {
        return $this->_resource->getPendingRecords();
    }

    /**
     * @param string $field
     * @return array
     * @throws LocalizedException
     */
    public function getProcessedEntries($field = '*')
    {
        return $this->_resource->getProcessedEntries($field);
    }

    /**
     * @param int $salesOrderId
     * @param string $entityType
     * @return string
     * @throws LocalizedException
     */
    public function getClientOrderId(int $salesOrderId, $entityType = OrderEntityType::RETURNS)
    {
        return $this->_resource->getClientOrderId($salesOrderId, $entityType);
    }

    /**
     * @param int $salesOrderId
     * @param array $entityType
     * @return array
     * @throws LocalizedException
     */
    public function getClientOrderIds(int $salesOrderId, array $entityType = [])
    {
        return $this->_resource->getClientOrderIds($salesOrderId, $entityType);
    }

    /**
     * @param $entityId
     * @param null $field
     * @return Api\Data\OrderExportInterface|OrderExport
     * @throws NoSuchEntityException
     */
    public function get($entityId, $field = null)
    {
        /** @var Api\Data\OrderExportInterface|OrderExport $entity */
        $entity = $this->_entityFactory->create();
        $this->_resource->load($entity, $entityId, $field);
        if (!$entity->getId()) {
            throw new NoSuchEntityException(__('The entity with ID "%1" doesn\'t exist.', $entityId));
        }

        return $entity;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getLastUpdatedAt()
    {
        return $this->_resource->getLastUpdatedAt();
    }

    /**
     * @param Api\Data\OrderExportInterface $entity
     * @return Api\Data\OrderExportInterface
     * @throws CouldNotSaveException
     */
    public function save(Api\Data\OrderExportInterface $entity)
    {
        try {
            $this->_resource->save($entity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $entity;
    }

    /**
     * @param array $entries
     * @return int
     * @throws CouldNotSaveException
     */
    public function saveMultiple(array $entries)
    {
        try {
            $result = $this->_resource->insertOnDuplicate($entries);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $result;
    }

    /**
     * @param Api\Data\OrderExportInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Api\Data\OrderExportInterface $entity)
    {
        try {
            $this->_resource->delete($entity);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($entityId)
    {
        return $this->delete($this->get($entityId));
    }
}
