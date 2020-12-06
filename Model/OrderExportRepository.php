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

/**
 * Class OrderExportRepository
 * @package SoftCommerce\Rebound\Model
 */
class OrderExportRepository implements Api\OrderExportRepositoryInterface
{
    /**
     * @var ResourceModel\OrderExport
     */
    private ResourceModel\OrderExport $resource;

    /**
     * @var OrderExportFactory
     */
    private OrderExportFactory $entityFactory;

    /**
     * @var ResourceModel\OrderExport\CollectionFactory
     */
    private ResourceModel\OrderExport\CollectionFactory $collectionFactory;

    /**
     * @var Api\Data\OrderExportSearchResultsInterface
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

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
        $this->resource = $resource;
        $this->entityFactory = $entityFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor
            ?: ObjectManager::getInstance()->get(CollectionProcessorInterface::class);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Api\Data\OrderExportSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var Api\Data\OrderExportSearchResultsInterface $searchResults */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
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
        $entity = $this->entityFactory->create();
        $this->resource->load($entity, $entityId, $field);
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
        return $this->resource->getLastUpdatedAt();
    }

    /**
     * @param Api\Data\OrderExportInterface $entity
     * @return Api\Data\OrderExportInterface
     * @throws CouldNotSaveException
     */
    public function save(Api\Data\OrderExportInterface $entity)
    {
        try {
            $this->resource->save($entity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $entity;
    }

    /**
     * @param Api\Data\OrderExportInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Api\Data\OrderExportInterface $entity)
    {
        try {
            $this->resource->delete($entity);
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
