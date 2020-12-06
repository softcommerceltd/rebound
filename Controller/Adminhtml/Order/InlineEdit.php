<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validation\ValidationException;
use SoftCommerce\Rebound\Api\Data\OrderExportInterface;
use SoftCommerce\Rebound\Api\OrderExportRepositoryInterface;
use SoftCommerce\Rebound\Model\OrderExportFactory;

/**
 * Class InlineEdit
 * @package SoftCommerce\Rebound\Controller\Adminhtml\Order
 */
class InlineEdit extends AbstractOrderList implements HttpPostActionInterface
{
    /**
     * @var DataObjectHelper
     */
    private DataObjectHelper $dataObjectHelper;

    /**
     * InlineEdit constructor.
     * @param DataObjectHelper $dataObjectHelper
     * @param DataPersistorInterface $dataPersistor
     * @param OrderExportFactory $postFactory
     * @param OrderExportRepositoryInterface $orderRepository
     * @param Context $context
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        DataPersistorInterface $dataPersistor,
        OrderExportFactory $postFactory,
        OrderExportRepositoryInterface $orderRepository,
        Context $context
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($dataPersistor, $postFactory, $orderRepository, $context);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $errorMessages = [];
        $request = $this->getRequest();
        $requestData = $request->getParam('items', []);

        if ($request->isXmlHttpRequest() && $request->isPost() && $requestData) {
            foreach ($requestData as $itemData) {
                try {
                    $postId = $itemData[OrderExportInterface::ENTITY_ID];
                    $postEntity = $this->orderRepository->get($postId);
                    $this->dataObjectHelper->populateWithArray($postEntity, $itemData, OrderExportInterface::class);
                    $this->orderRepository->save($postEntity);
                } catch (NoSuchEntityException $e) {
                    $errorMessages[] = __('[ID: %value] Could not retrieve post ID: %1.', $postId);
                } catch (ValidationException $e) {
                    foreach ($e->getErrors() as $localizedError) {
                        $errorMessages[] = __('[ID: %value] %message', [
                            'value' => $postId,
                            'message' => $localizedError->getMessage()
                        ]);
                    }
                } catch (CouldNotSaveException $e) {
                    $errorMessages[] = __(
                        '[ID: %value] %message',
                        [
                            'value' => $postId,
                            'message' => $e->getMessage()
                        ]
                    );
                }
            }
        } else {
            $errorMessages[] = __('Please correct the request data.');
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData([
            'messages' => $errorMessages,
            'error' => count($errorMessages),
        ]);

        return $resultJson;
    }
}
