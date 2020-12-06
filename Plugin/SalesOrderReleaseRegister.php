<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Rebound\Plugin;

use Magento\Sales\Model\Service\OrderService;
use SoftCommerce\Rebound\Model\Source\Status;

/**
 * Class SalesOrderReleaseRegister
 * @package SoftCommerce\Rebound\Plugin
 */
class SalesOrderReleaseRegister extends RegistrarAbstract
{
    /**
     * @param OrderService $subject
     * @param bool $result
     * @param $orderId
     * @return bool
     */
    public function afterUnHold(OrderService $subject, bool $result, $orderId): bool
    {
        if ($result) {
            $this->execute([$orderId], Status::PENDING);
        }

        return $result;
    }
}
