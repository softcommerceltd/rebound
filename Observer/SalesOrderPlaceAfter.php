<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use SoftCommerce\Core\Model\Source\Status;
use SoftCommerce\Rebound\Api\Data\OrderExportInterface;

/**
 * Class SalesOrderPlaceAfter
 * @package Plenty\Order\Observer
 */
class SalesOrderPlaceAfter implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        if (!$order || !$order instanceof Order) {
            return;
        }

        $order->setData(OrderExportInterface::REBOUND_ORDER_STATUS, Status::PENDING);
    }
}
