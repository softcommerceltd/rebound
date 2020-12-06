<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use SoftCommerce\Rebound\Model\OrderExport;

/**
 * Interface OrderExportRepositoryInterface
 * @package SoftCommerce\Rebound\Api
 */
interface OrderExportRepositoryInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\OrderExportSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param $entityId
     * @param null $field
     * @return Data\OrderExportInterface|OrderExport
     * @throws NoSuchEntityException
     */
    public function get($entityId, $field = null);

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getLastUpdatedAt();

    /**
     * @param Data\OrderExportInterface $entity
     * @return Data\OrderExportInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\OrderExportInterface $entity);

    /**
     * @param Data\OrderExportInterface $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\OrderExportInterface $entity);

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($entityId);
}
