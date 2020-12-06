<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Rebound\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Collection;
use Magento\Ui\Component\MassAction\Filter;
use SoftCommerce\Rebound\Model\ResourceModel;
use SoftCommerce\Rebound\Model\ResourceModel\OrderExport\CollectionFactory;

/**
 * Class AbstractMassAction
 * @package SoftCommerce\Rebound\Controller\Adminhtml\Order
 */
abstract class AbstractMassAction extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'SoftCommerce_Rebound::order_list';
    const REDIRECT_URL = 'softcommerce_rebound/order/index';

    /**
     * @var Filter
     */
    protected Filter $massActionFilter;

    /**
     * @var ResourceModel\OrderExport
     */
    protected ResourceModel\OrderExport $resource;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;


    public function __construct(
        Filter $massActionFilter,
        ResourceModel\OrderExport $resource,
        ResourceModel\OrderExport\CollectionFactory $collectionFactory,
        Context $context
    ) {
        $this->massActionFilter = $massActionFilter;
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return Redirect|ResponseInterface|Result\Redirect|ResultInterface|mixed
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(self::REDIRECT_URL);
        if (!$this->getRequest()->isPost()) {
            $this->messageManager->addErrorMessage(__('Wrong request.'));
            return $resultRedirect;
        }

        try {
            $collection = $this->massActionFilter
                ->getCollection($this->collectionFactory->create());
            return $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath(self::REDIRECT_URL);
        }
    }

    /**
     * @param Collection $collection
     * @return mixed
     */
    abstract protected function massAction(Collection $collection);

    /**
     * @return string|null
     */
    protected function getComponentRefererUrl()
    {
        return $this->massActionFilter->getComponentRefererUrl() ?: self::REDIRECT_URL;
    }
}
