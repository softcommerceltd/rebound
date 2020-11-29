<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Rebound\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use SoftCommerce\Rebound\Api;
use SoftCommerce\Rebound\Helper\Data as Helper;
use SoftCommerce\Rebound\Logger\Logger;

/**
 * Class OrderExportAbstractManagement
 * @package SoftCommerce\Rebound\Model
 * @deprecared
 */
abstract class OrderExportAbstractManagement
{
    /**
     * @var DateTime
     */
    protected DateTime $dateTime;

    /**
     * @var Helper
     */
    protected Helper $helper;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var Api\OrderExportRepositoryInterface
     */
    protected Api\OrderExportRepositoryInterface $orderExportRepository;

    /**
     * @var OrderExportFactory
     */
    protected OrderExportFactory $orderExportFactory;

    /**
     * @var FilterBuilder
     */
    protected FilterBuilder $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    protected FilterGroupBuilder $filterGroupBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var Json
     */
    protected Json $serializer;

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
     * OrderExportAbstractManagement constructor.
     * @param Api\OrderExportRepositoryInterface $orderExportRepository
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Helper $helper
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param Json $serializer
     */
    public function __construct(
        Api\OrderExportRepositoryInterface $orderExportRepository,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Helper $helper,
        DateTime $dateTime,
        Logger $logger,
        Json $serializer
    ) {
        $this->orderExportRepository = $orderExportRepository;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->helper = $helper;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    public function getErrors($key = null)
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
    public function setErrors($data, $key = null)
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
     * @throws LocalizedException
     * @throws \Exception
     */
    public function getLastCollectedAt()
    {
        return $this->formatDateTime(
            $this->orderExportRepository->getLastUpdatedAt()
        );
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
     * @param string|null $dateTime
     * @return string|null
     * @throws \Exception
     */
    private function formatDateTime($dateTime)
    {
        $w3cResult = null;
        if (strtotime($dateTime) > 0) {
            $w3cResult = $this->helper->getDateTimeLocale($dateTime);
        }

        return $w3cResult;
    }
}
