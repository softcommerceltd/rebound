<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Service\OrderExport\Processor;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use SoftCommerce\Rebound\Model\Source\OrderEntityType;
use SoftCommerce\Rebound\Service\OrderExport\ProcessorInterface;

/**
 * Class Recycling
 * @package SoftCommerce\Rebound\Service\OrderExport\Processor
 */
class Recycling extends AbstractProcessor implements ProcessorInterface
{
    /**
     * @var string|null
     */
    protected ?string $entityType = OrderEntityType::RECYCLING;

    /**
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        return parent::execute();
    }
}
