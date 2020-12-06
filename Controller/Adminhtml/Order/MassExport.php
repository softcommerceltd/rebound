<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Collection;
use Magento\Framework\Phrase;
use Magento\Ui\Component\MassAction\Filter;
use SoftCommerce\Rebound\Api;
use SoftCommerce\Rebound\Model\ResourceModel;
use SoftCommerce\Rebound\Model\ResourceModel\OrderExport\CollectionFactory;
use SoftCommerce\Rebound\Model\Source\Status;
use SoftCommerce\Rebound\Service\OrderExportInterface;

/**
 * Class MassExport
 * @package SoftCommerce\Rebound\Controller\Adminhtml\Order
 */
class MassExport extends AbstractMassAction implements HttpPostActionInterface
{
    /**
     * @var OrderExportInterface
     */
    private OrderExportInterface $orderExportService;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * MassExport constructor.
     * @param OrderExportInterface $orderExportService
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Filter $massActionFilter
     * @param ResourceModel\OrderExport $resource
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     */
    public function __construct(
        OrderExportInterface $orderExportService,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Filter $massActionFilter,
        ResourceModel\OrderExport $resource,
        CollectionFactory $collectionFactory,
        Context $context
    ) {
        $this->orderExportService = $orderExportService;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($massActionFilter, $resource, $collectionFactory, $context);
    }

    /**
     * @param Collection $collection
     * @return Redirect|mixed
     */
    protected function massAction(Collection $collection)
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        if (!$ids = $collection->getColumnValues(Api\Data\OrderExportInterface::ORDER_ID)) {
            $this->messageManager->addErrorMessage(__('Could not retrieve request ID(s) data for submission.'));
            return $resultRedirect;
        }

        $this->orderExportService->setSearchCriteria(
            $this->searchCriteriaBuilder
                ->addFilter(Api\Data\OrderExportInterface::ENTITY_ID, array_unique($ids), 'in')
                ->create()
        );

        try {
            $this->orderExportService->execute();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $this->buildMessageResponseHtml($this->orderExportService->getResponse());

        return $resultRedirect;
    }

    /**
     * @param $response
     * @return $this
     */
    private function buildMessageResponseHtml($response)
    {
        if (!is_array($response)) {
            $this->messageManager->addSuccessMessage($response);
            return $this;
        }

        foreach ($response as $status => $message) {
            if (is_array($message)) {
                $this->buildMessageResponseHtml($message);
                continue;
            }

            if ($message instanceof Phrase) {
                $message = $message->render();
            }

            if ($status === Status::ERROR) {
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
