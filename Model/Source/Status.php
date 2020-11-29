<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 * @package SoftCommerce\Rebound\Model\Source
 */
class Status implements OptionSourceInterface
{
    const ERROR             = 'error';
    const PENDING           = 'pending';
    const COMPLETE          = 'complete';
    const PROCESSING        = 'processing';
    const SUCCESS           = 'success';
    const NOTICE            = 'notice';
    const WARNING           = 'warning';
    const PAP               = 'pap';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            self::ERROR,
            self::PENDING,
            self::COMPLETE,
            self::PROCESSING,
            self::SUCCESS,
            self::WARNING
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ERROR, 'label' => __('Error')],
            ['value' => self::PENDING, 'label' => __('Pending')],
            ['value' => self::COMPLETE, 'label' => __('Complete')],
            ['value' => self::PROCESSING, 'label' => __('Processing')],
            ['value' => self::SUCCESS, 'label' => __('Success')]
        ];
    }
}
