<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Rebound\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use SoftCommerce\Rebound\Api\Data\OrderExportInterface;
use SoftCommerce\Rebound\Model\ResourceModel;

/**
 * Class OrderExport
 * @package SoftCommerce\Rebound\Model
 */
class OrderExport extends AbstractModel implements OrderExportInterface, IdentityInterface
{
    const CACHE_TAG = 'softcommerce_rebound_orderexport';
    protected $_cacheTag = 'softcommerce_rebound_orderexport';
    protected $_eventPrefix = 'softcommerce_rebound_orderexport';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\OrderExport::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return string|null
     */
    public function getEntityType(): ?string
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    /**
     * @param string $entityType
     * @return $this
     */
    public function setEntityType(string $entityType)
    {
        $this->setData(self::ENTITY_TYPE, $entityType);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * @param int $incrementId
     * @return $this
     */
    public function setIncrementId(int $incrementId)
    {
        $this->setData(self::INCREMENT_ID, $incrementId);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getExternalId()
    {
        return $this->getData(self::EXTERNAL_ID);
    }

    /**
     * @param int $externalId
     * @return $this
     */
    public function setExternalId(int $externalId)
    {
        $this->setData(self::EXTERNAL_ID, $externalId);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status)
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->setData(self::MESSAGE, $message);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRequestEntry()
    {
        return $this->getData(self::REQUEST_ENTRY);
    }

    /**
     * @param $request
     * @return $this
     */
    public function setRequestEntry($request)
    {
        $this->setData(self::REQUEST_ENTRY, $request);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getResponseEntry()
    {
        return $this->getData(self::RESPONSE_ENTRY);
    }

    /**
     * @param $response
     * @return $this
     */
    public function setResponseEntry($response)
    {
        $this->setData(self::RESPONSE_ENTRY, $response);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
        return $this;
    }
}
