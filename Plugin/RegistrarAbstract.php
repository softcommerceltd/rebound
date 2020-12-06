<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Rebound\Plugin;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use SoftCommerce\Rebound\Api\Data\OrderExportInterface;

/**
 * Class RegistrarAbstract
 * @package SoftCommerce\Rebound\Plugin
 */
class RegistrarAbstract
{
    /**
     * @var AdapterInterface|null
     */
    private ?AdapterInterface $connection;

    /**
     * RegistrarAbstract constructor.
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->connection = $resource->getConnection();
    }

    /**
     * @param array $orderId
     * @param string $status
     * @return int
     */
    protected function execute(array $orderId, string $status)
    {
        return $this->connection->update(
            $this->connection->getTableName('sales_order'),
            [OrderExportInterface::REBOUND_ORDER_STATUS => $status],
            ['entity_id in (?)' => $orderId]
        );
    }
}
