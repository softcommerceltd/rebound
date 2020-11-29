<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class OrderEntityType
 * @package SoftCommerce\Rebound\Model\Source
 */
class OrderEntityType implements OptionSourceInterface
{
    const RETURNS = 'returns';
    const RECYCLING = 'recycling';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            self::RETURNS,
            self::RECYCLING
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::RETURNS, 'label' => __('Returns')],
            ['value' => self::RECYCLING, 'label' => __('Recycling')]
        ];
    }
}
