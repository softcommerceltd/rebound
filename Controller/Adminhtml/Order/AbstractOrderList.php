<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Rebound\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use SoftCommerce\Rebound\Api\Data\OrderExportInterface;
use SoftCommerce\Rebound\Api\OrderExportRepositoryInterface;
use SoftCommerce\Rebound\Model\OrderExport;
use SoftCommerce\Rebound\Model\OrderExportFactory;

/**
 * Class AbstractOrderList
 * @package SoftCommerce\Rebound\Controller\Adminhtml\Order
 */
abstract class AbstractOrderList extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'SoftCommerce_Rebound::order_list';

    /**
     * @var DataPersistorInterface
     */
    protected DataPersistorInterface $dataPersistor;

    /**
     * @var OrderExportFactory
     */
    protected OrderExportFactory $orderFactory;

    /**
     * @var OrderExportRepositoryInterface
     */
    protected OrderExportRepositoryInterface $orderRepository;

    /**
     * @var OrderExportInterface|OrderExport|null
     */
    protected $orderEntity;

    /**
     * AbstractOrderList constructor.
     * @param DataPersistorInterface $dataPersistor
     * @param OrderExportFactory $postFactory
     * @param OrderExportRepositoryInterface $orderRepository
     * @param Context $context
     */
    public function __construct(
        DataPersistorInterface $dataPersistor,
        OrderExportFactory $postFactory,
        OrderExportRepositoryInterface $orderRepository,
        Context $context
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->orderFactory = $postFactory;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }

    /**
     * @return OrderExportInterface|OrderExport|null
     */
    protected function initEntity()
    {
        $orderId = $this->getRequest()->getParam(OrderExportInterface::ENTITY_ID)
            ?: ($this->getRequest()->getParam('general')[OrderExportInterface::ENTITY_ID] ?? null);

        if ($orderId) {
            try {
                $this->orderEntity = $this->orderRepository->get($orderId);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Could not find order with ID: %1.', $orderId));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $this->orderEntity;
            }
        } else {
            $this->orderEntity = $this->orderFactory->create();
        }

        return $this->orderEntity;
    }
}
