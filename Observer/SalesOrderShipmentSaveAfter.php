<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Shipment;
use SoftCommerce\Core\Model\Source\Status;
use SoftCommerce\Rebound\Api\Data\OrderExportInterface;

/**
 * Class SalesOrderPlaceAfter
 * @package Plenty\Order\Observer
 */
class SalesOrderShipmentSaveAfter implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var Shipment $shipment */
        $shipment = $observer->getEvent()->getShipment();
        if (!$shipment || !$shipment instanceof Shipment || $shipment->getOrigData('entity_id')) {
            return;
        }

        try {
            $shipment->getOrder()->setData(OrderExportInterface::REBOUND_ORDER_STATUS, Status::PENDING);
        } catch (\Exception $e) {
        }
    }
}
