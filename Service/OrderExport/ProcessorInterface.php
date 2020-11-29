<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Service\OrderExport;

use SoftCommerce\Rebound\Service\OrderExportInterface;
use SoftCommerce\Rebound\Service\OrderExport;

/**
 * Interface ProcessorInterface
 * @package SoftCommerce\Rebound\Service\OrderExport
 */
interface ProcessorInterface extends OrderExportInterface
{
    /**
     * @param $context
     * @return $this
     */
    public function init(OrderExport $context);

    /**
     * @return OrderExport
     */
    public function getContext(): OrderExport;
}
