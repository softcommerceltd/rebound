<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Service;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface OrderExportInterface
 * @package SoftCommerce\Rebound\Service
 */
interface OrderExportInterface
{
    const PROCESS_SIZE = 1000;
    const ENTITY_FILTER = 'entity_filter';
    const SEARCH_CRITERIA = 'search_criteria';

    /**
     * @param null $key
     * @return array|mixed
     */
    public function getError($key = null);

    /**
     * @param int|string|array $data
     * @param int|string|null $key
     * @return $this
     */
    public function setError($data, $key = null);

    /**
     * @param null $key
     * @return array|mixed
     */
    public function getResponse($key = null);

    /**
     * @param string|array $data
     * @param null|string $key
     * @return $this
     */
    public function setResponse($data, $key = null);

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    public function addResponse($data, $key = null);

    /**
     * @param int|string|null $key
     * @return array|string|mixed
     */
    public function getRequest($key = null);

    /**
     * @param $value
     * @param null $key
     * @return $this
     */
    public function setRequest($value, $key = null);

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    public function addRequest($data, $key = null);

    /**
     * @return SearchCriteriaInterface
     */
    public function getSearchCriteria();

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria);

    /**
     * @return array
     */
    public function getEntityFilter(): array;

    /**
     * @param array $entityType
     * @return $this
     */
    public function setEntityFilter(array $entityType);

    /**
     * @return int|null
     */
    public function getProcessSize(): ?int;

    /**
     * @param int $processSize
     * @return $this
     */
    public function setProcessSize(int $processSize);

    /**
     * @return $this
     */
    public function execute();
}
