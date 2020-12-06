<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Rebound\Api\Data;

/**
 * Interface OrderExportInterface
 * @package SoftCommerce\Rebound\Api\Data
 */
interface OrderExportInterface
{
    const DB_TABLE_NAME = 'softcommerce_rebound_order_entity';

    // Model metadata
    const ENTITY_ID = 'entity_id';
    const ENTITY_TYPE = 'entity_type';
    const ORDER_ID = 'order_id';
    const INCREMENT_ID = 'increment_id';
    const EXTERNAL_ID = 'external_id';
    const REFERENCE_ID = 'reference_id';
    const STATUS = 'status';
    const MESSAGE = 'message';
    const REQUEST_ENTRY = 'request_entry';
    const RESPONSE_ENTRY = 'response_entry';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    // Sales Order entity field
    const REBOUND_ORDER = 'rebound_order';
    const REBOUND_ORDER_STATUS = 'rebound_order_status';

    /**
     * @return string|null
     */
    public function getEntityType(): ?string;

    /**
     * @param string $entityType
     * @return $this
     */
    public function setEntityType(string $entityType);

    /**
     * @return int|null
     */
    public function getOrderId(): int;

    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId(int $orderId);

    /**
     * @return string|null
     */
    public function getIncrementId();

    /**
     * @param int $incrementId
     * @return $this
     */
    public function setIncrementId(int $incrementId);

    /**
     * @return int|null
     */
    public function getExternalId();

    /**
     * @param int $externalId
     * @return $this
     */
    public function setExternalId(int $externalId);

    /**
     * @return int|string|null
     */
    public function getReferenceId();

    /**
     * @param int|string|null $referenceId
     * @return $this
     */
    public function setReferenceId($referenceId);

    /**
     * @return string|null
     */
    public function getStatus();

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status);

    /**
     * @return string|null
     */
    public function getMessage();

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * @return string|null
     */
    public function getRequestEntry();

    /**
     * @param $request
     * @return $this
     */
    public function setRequestEntry($request);

    /**
     * @return string|null
     */
    public function getResponseEntry();

    /**
     * @param $response
     * @return $this
     */
    public function setResponseEntry($response);

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @param $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}
