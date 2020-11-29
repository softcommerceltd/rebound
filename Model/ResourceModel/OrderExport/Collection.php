<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Rebound\Model\ResourceModel\OrderExport;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SoftCommerce\Rebound\Api\Data\OrderExportInterface;
use SoftCommerce\Rebound\Model\OrderExport;
use SoftCommerce\Rebound\Model\ResourceModel;

/**
 * Class Collection
 * @package SoftCommerce\Rebound\Model\ResourceModel\OrderExport
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = OrderExportInterface::ENTITY_ID;

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(OrderExport::class, ResourceModel\OrderExport::class);
    }
}
