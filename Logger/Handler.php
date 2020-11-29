<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Logger;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;

/**
 * Class Handler
 * @package SoftCommerce\Rebound\Logger
 */
class Handler extends Base
{
    /**
     * File Name
     * @var string
     */
    protected $fileName = '/var/log/softcommerce/rebound.log';

    /**
     * Handler constructor.
     * @param DriverInterface $filesystem
     * @param string|null $filePath
     * @param string|null $fileName
     * @throws \Exception
     */
    public function __construct(DriverInterface $filesystem, ?string $filePath = null, ?string $fileName = null)
    {
        parent::__construct($filesystem, $filePath, $fileName);
    }
}
