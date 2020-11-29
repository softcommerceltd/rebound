<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order as SalesOrder;

/**
 * Interface OrderExportManagementInterface
 * @package SoftCommerce\Rebound\Api
 * @deprecared
 */
interface OrderExportManagementInterface
{
    const PROCESS_SIZE = 2000;

    /**
     * @param null $key
     * @return array|mixed
     */
    public function getErrors($key = null);

    /**
     * @param int|string|array $data
     * @param int|string|null $key
     * @return $this
     */
    public function setErrors($data, $key = null);

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
     * @return string|null
     * @throws LocalizedException
     * @throws \Exception
     */
    public function getLastCollectedAt();


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
     * @param bool $keys
     * @return array
     */
    public function getTargetIds($keys = false);

    /**
     * @param array $targetIds
     * @return $this
     */
    public function setTargetIds(array $targetIds);

    /**
     * @return SalesOrder
     * @throws LocalizedException
     */
    public function getSalesOrder();

    /**
     * @return $this
     */
    public function execute();
}
