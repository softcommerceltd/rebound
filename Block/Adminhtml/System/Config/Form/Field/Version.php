<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\PackageInfo;

/**
 * Class Version
 * @package SoftCommerce\Rebound\Block\Adminhtml\System\Config\Form\Field
 */
class Version extends Field
{
    /**
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * Version constructor.
     * @param Context $context
     * @param PackageInfo $packageInfo
     * @param array $data
     */
    public function __construct(
        Context $context,
        PackageInfo $packageInfo,
        array $data = []
    ) {
        $this->packageInfo = $packageInfo;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setData('text', $this->packageInfo->getVersion('SoftCommerce_Rebound'));
        return parent::_getElementHtml($element);
    }
}
