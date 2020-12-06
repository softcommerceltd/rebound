<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Controller\Adminhtml\Sales\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderInterface;
use SoftCommerce\Rebound\Model\Source\Status;
use SoftCommerce\Rebound\Service\OrderExportInterface;

/**
 * Class Export
 * @package SoftCommerce\Rebound\Controller\Adminhtml\Sales\Order
 */
class Export extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'SoftCommerce_Rebound::sales_order_rebound_export';

    const REDIRECT_SALES_ORDER_INDEX = 'sales/order';

    /**
     * @var OrderExportInterface
     */
    private OrderExportInterface $orderExportService;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * Export constructor.
     * @param Context $context
     * @param OrderExportInterface $orderExportService
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        OrderExportInterface $orderExportService,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->orderExportService = $orderExportService;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$orderId = $this->getRequest()->getParam('order_id', [])) {
            $this->messageManager->addErrorMessage(__('Could not retrieve order ID from request data.'));
            $resultRedirect->setPath(self::REDIRECT_SALES_ORDER_INDEX);
            return $resultRedirect;
        }

        try {
            $this->orderExportService->setSearchCriteria(
                $this->searchCriteriaBuilder
                    ->addFilter(OrderInterface::ENTITY_ID, $orderId, 'eq')
                    ->create()
            )->execute();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath(self::REDIRECT_SALES_ORDER_INDEX);
            return $resultRedirect;
        }

        $this->executeAfter($this->orderExportService->getResponse());
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::REDIRECT_SALES_ORDER_INDEX . '/view', ['order_id' => $orderId]);
        return $resultRedirect;
    }

    /**
     * @param $response
     * @return $this
     */
    private function executeAfter($response)
    {
        if (!is_array($response)) {
            $this->messageManager->addSuccessMessage($response);
            return $this;
        }

        foreach ($response as $status => $message) {
            if (is_array($message)) {
                $this->executeAfter($message);
                continue;
            }

            if ($message instanceof Phrase) {
                $message = $message->render();
            }

            if ($status === \SoftCommerce\Avocado\Model\Source\Status::ERROR) {
                $this->messageManager->addErrorMessage($message);
            } elseif ($status === Status::WARNING) {
                $this->messageManager->addWarningMessage($message);
            } elseif ($status === Status::NOTICE) {
                $this->messageManager->addNoticeMessage($message);
            } else {
                $this->messageManager->addSuccessMessage($message);
            }
        }

        return $this;
    }
}
