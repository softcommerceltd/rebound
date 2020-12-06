<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Controller\Adminhtml\Sales\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use SoftCommerce\Rebound\Api\Data\OrderExportInterface;
use SoftCommerce\Rebound\Model\Source\Status;

/**
 * Class MassScheduleExport
 * @package SoftCommerce\Rebound\Controller\Adminhtml\Sales\Order
 */
class MassScheduleExport extends AbstractMassAction implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'SoftCommerce_Rebound::sales_order_rebound_export';

    /**
     * @var string
     */
    protected $redirectUrl = 'sales/order/';

    /**
     * @var AdapterInterface|null
     */
    private ?AdapterInterface $connection;

    public function __construct(
        CollectionFactory $collectionFactory,
        ResourceConnection $resource,
        Context $context,
        Filter $filter
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->connection = $resource->getConnection();
    }

    /**
     * @param AbstractCollection $collection
     * @return ResponseInterface|Redirect|ResultInterface
     */
    protected function massAction(AbstractCollection $collection)
    {
        if (!$ids = $collection->getAllIds()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($this->redirectUrl);
            return $resultRedirect;
        }

        try {
            $result = $this->connection->update(
                $this->connection->getTableName('sales_order'),
                [OrderExportInterface::REBOUND_ORDER_STATUS => Status::PENDING],
                ['entity_id in (?)' => $ids]
            );

            $this->messageManager
                ->addSuccessMessage(__('A total of %1 orders have been scheduled for export.', $result));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($this->redirectUrl);
            return $resultRedirect;
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->redirectUrl);
        return $resultRedirect;
    }
}
