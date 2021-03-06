<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Rebound\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use SoftCommerce\Rebound\Api\Data\OrderExportInterface;
use SoftCommerce\Rebound\Model\Source\OrderEntityType;
use SoftCommerce\Rebound\Model\Source\Status;

/**
 * Class OrderExport
 * @package SoftCommerce\Rebound\Model\ResourceModel
 */
class OrderExport extends AbstractDb
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(OrderExportInterface::DB_TABLE_NAME, OrderExportInterface::ENTITY_ID);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getLastUpdatedAt()
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), [OrderExportInterface::UPDATED_AT])
            ->order(OrderExportInterface::UPDATED_AT . ' ' . Select::SQL_DESC);

        return $adapter->fetchOne($select);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getPendingRecords()
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), [OrderExportInterface::ORDER_ID, OrderExportInterface::EXTERNAL_ID])
            ->where(OrderExportInterface::STATUS . ' = ?', Status::PENDING);

        return $adapter->fetchPairs($select);
    }

    /**
     * @param string $field
     * @return array
     * @throws LocalizedException
     */
    public function getProcessedEntries($field = '*')
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), $field)
            ->where(OrderExportInterface::STATUS . ' = ?', Status::COMPLETE);

        return $adapter->fetchAll($select);
    }

    /**
     * @param int $salesOrderId
     * @param string $entityType
     * @return string
     * @throws LocalizedException
     */
    public function getClientOrderId(int $salesOrderId, $entityType = OrderEntityType::RETURNS)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), [OrderExportInterface::EXTERNAL_ID])
            ->where(OrderExportInterface::ORDER_ID . ' = ?', $salesOrderId)
            ->where(OrderExportInterface::ENTITY_TYPE . ' = ?', $entityType);

        return $adapter->fetchOne($select);
    }

    /**
     * @param int $salesOrderId
     * @param array $entityType
     * @return array
     * @throws LocalizedException
     */
    public function getClientOrders(int $salesOrderId, array $entityType = [])
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where(OrderExportInterface::ORDER_ID . ' = ?', $salesOrderId);

        if (!empty($entityType)) {
            $select->where(OrderExportInterface::ENTITY_TYPE . ' in (?)', $entityType);
        }

        return $adapter->fetchAll($select);
    }

    /**
     * @param array $data
     * @param array $fields
     * @return int
     * @throws LocalizedException
     */
    public function insertOnDuplicate(array $data, array $fields = [])
    {
        return $this->getConnection()
            ->insertOnDuplicate($this->getMainTable(), $data, $fields);
    }

    /**
     * @param array $bind
     * @param string $where
     * @return $this
     * @throws LocalizedException
     */
    public function update(array $bind, $where = '')
    {
        $this->getConnection()
            ->update($this->getMainTable(), $bind, $where);
        return $this;
    }

    /**
     * @param $where
     * @return int
     * @throws LocalizedException
     */
    public function remove($where)
    {
        return $this->getConnection()->delete($this->getMainTable(), $where);
    }
}
