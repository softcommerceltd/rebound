<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Cron\Backend;

use SoftCommerce\Rebound\Service\OrderExportInterface;
use SoftCommerce\Rebound\Logger\Logger;

/**
 * Class OrderExportService
 * @package SoftCommerce\Rebound\Cron\Backend
 */
class OrderExportService
{
    /**
     * @var OrderExportInterface
     */
    private OrderExportInterface $orderExportService;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * OrderExportService constructor.
     * @param OrderExportInterface $orderExportService
     * @param Logger $logger
     */
    public function __construct(
        OrderExportInterface $orderExportService,
        Logger $logger
    ) {
        $this->orderExportService = $orderExportService;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        try {
            $this->orderExportService->execute();
        } catch (\Exception $e) {
            $this->logger->debug(__METHOD__, ['error' => $e->getMessage()]);
        }
    }
}
