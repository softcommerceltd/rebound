<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Plugin;

use Magento\Framework\Phrase;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\View;

/**
 * Class SalesOrderViewExportActionProviderPlugin
 * @package SoftCommerce\Rebound\Plugin
 */
class SalesOrderViewExportActionProviderPlugin
{
    /**
     * @param View $subject
     * @param LayoutInterface $layout
     */
    public function beforeSetLayout(View $subject, LayoutInterface $layout): void
    {
        if ($subject->getOrderId()) {
            $subject->addButton(
                'rebound_order_export',
                [
                    'label' => $this->getBtnLabel(),
                    'class' => 'export',
                    'onclick' => "confirmSetLocation('{$this->getConfirmMessage()}', '{$this->getUrl($subject)}')"
                ],
                0,
                10
            );
        }
    }

    /**
     * @param View $subject
     * @return string
     */
    private function getUrl(View $subject)
    {
        return $subject->getUrl("softcommerce_rebound/sales_order/export", ['_current' => true]);
    }

    /**
     * @return Phrase
     */
    private function getBtnLabel()
    {
        return __('Export to Rebound');
    }

    /**
     * @return Phrase
     */
    private function getConfirmMessage()
    {
        return __('Export order to Rebound?');
    }
}
