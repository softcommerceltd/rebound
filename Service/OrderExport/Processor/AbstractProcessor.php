<?php
/**
 * Copyright © Soft Commerce Ltd, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SoftCommerce\Rebound\Service\OrderExport\Processor;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use SoftCommerce\Rebound\Api\Data\OrderExportInterface;
use SoftCommerce\Rebound\Http\ClientInterface;
use SoftCommerce\Rebound\Http\ClientInterfaceFactory;
use SoftCommerce\Rebound\Http\OrderInterface as ClientOrderInterface;
use SoftCommerce\Rebound\Logger\Logger;
use SoftCommerce\Rebound\Model\ConfigInterface;
use SoftCommerce\Rebound\Model\ConfigInterfaceFactory;
use SoftCommerce\Rebound\Model\Source\Status;
use SoftCommerce\Rebound\Service\OrderExport\AbstractService;

/**
 * Class AbstractProcessor
 * @package SoftCommerce\Rebound\Service\OrderExport\Processor
 */
class AbstractProcessor extends AbstractService
{
    /**
     * @var ClientInterface
     */
    protected ClientInterface $client;

    /**
     * @var ConfigInterface
     */
    protected ConfigInterface $config;

    /**
     * @var string|null
     */
    protected ?string $entityType = null;

    /**
     * AbstractProcessor constructor.
     * @param ClientInterfaceFactory $clientFactory
     * @param ConfigInterfaceFactory $configFactory
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param Json $serializer
     */
    public function __construct(
        ClientInterfaceFactory $clientFactory,
        ConfigInterfaceFactory $configFactory,
        DateTime $dateTime,
        Logger $logger,
        Json $serializer
    ) {
        $this->config = $configFactory->create(['data' => [ConfigInterface::CONFIG_ENTITY => $this->entityType]]);
        $this->client = $clientFactory->create(
            [
                'data' => [
                    ClientInterface::REQUEST_LOGIN => $this->config->getClientUsername(),
                    ClientInterface::REQUEST_API_KEY => $this->config->getClientAccessToken()
                ]
            ]
        );
        parent::__construct($dateTime, $logger, $serializer);
    }

    /**
     * @return AbstractProcessor
     * @throws LocalizedException
     */
    public function executeBefore()
    {
        $this->clientResponse = [];
        $isExistingEntry = (bool) $this->getContext()
            ->getClientOrder($this->entityType, OrderExportInterface::EXTERNAL_ID);
        if ($isExistingEntry) {
            $this->getContext()->setRequest(true, ClientOrderInterface::OVERWRITE_DATA);
        }

        return parent::executeBefore();
    }

    /**
     * @return $this|Recycling
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $this->executeBefore();
        if (!$this->canProcess() || !$request = $this->getContext()->getRequest()) {
            throw new LocalizedException(
                __(
                    'Could not export. [Order: %1, Entity: %2. Reason: %3]',
                    $this->getContext()->getSalesOrder()->getIncrementId(),
                    $this->entityType,
                    $this->getContext()->getRequest() ? 'Not allowed for export.' : 'Request data is not set.'
                )
            );
        }

        $this->client->execute(
            $this->config->getClientOrderCreateUrl(),
            ['order' => $request]
        );

        $this->clientResponse = $this->client->getResponseContents();
        $this->executeAfter();
        return $this;
    }

    /**
     * @return AbstractProcessor
     * @throws LocalizedException
     */
    public function executeAfter()
    {
        $salesOrder = $this->getContext()->getSalesOrder();

        if (empty($this->clientResponse)) {
            throw new LocalizedException(
                __(
                    'Could not retrieve order response data. [Order: %1, Entity: %2]',
                    $salesOrder->getIncrementId(),
                    $this->entityType
                )
            );
        }

        if (isset($this->clientResponse[Status::ERROR])) {
            throw new LocalizedException(
                __(
                    'Could not export order. %1',
                    implode(', ', $this->clientResponse[Status::ERROR][ClientOrderInterface::MESSAGE] ?? [])
                )
            );
        }

        $response = $this->clientResponse[Status::SUCCESS] ?? [];
        if (!isset($response[ClientOrderInterface::ORDER_ID])
            || !$clientOrderId = $response[ClientOrderInterface::ORDER_ID]
        ) {
            throw new LocalizedException(
                __(
                    'Could not retrieve order external ID. [Order: %1, Entity: %2]',
                    $salesOrder->getIncrementId(),
                    $this->entityType
                )
            );
        }

        $message = $response[ClientOrderInterface::MESSAGE] ?? 'Order has been created';
        $this->addClientEntry($clientOrderId, Status::COMPLETE, $message)
            ->getContext()
            ->addResponse(
                [
                    'order_id' => $salesOrder->getIncrementId(),
                    'client_id' => $clientOrderId,
                    'entity' => $this->entityType,
                    'message' => $message
                ],
                Status::SUCCESS
            );

        return parent::executeAfter();
    }

    /**
     * @param int $clientOrderId
     * @param string $status
     * @param $message
     * @return $this
     * @throws LocalizedException
     */
    public function addClientEntry(int $clientOrderId, string $status, $message)
    {
        $this->getContext()
            ->addToClientOrder(
                $this->entityType,
                [
                    OrderExportInterface::EXTERNAL_ID => $clientOrderId,
                    OrderExportInterface::STATUS => $status,
                    OrderExportInterface::MESSAGE => $message,
                    OrderExportInterface::REQUEST_ENTRY => $this->serializer
                        ->serialize($this->getContext()->getRequest()),
                    OrderExportInterface::RESPONSE_ENTRY => $this->serializer
                        ->serialize($this->clientResponse ?: [])
                ]
            );

        return $this;
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    protected function canProcess(): bool
    {
        return (!$this->getContext()->getSalesOrderReboundId($this->entityType)
                && $this->getContext()->getSalesOrderReboundStatus() === Status::PENDING)
            || in_array($this->entityType, $this->getContext()->getEntityFilter())
            || !empty($this->getContext()->getSearchCriteria());
    }
}
