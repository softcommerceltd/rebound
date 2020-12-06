<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Rebound\Service\OrderExport;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use SoftCommerce\Rebound\Api\Data\OrderExportInterface;
use SoftCommerce\Rebound\Logger\Logger;
use SoftCommerce\Rebound\Model\Source\Status;
use SoftCommerce\Rebound\Service\OrderExport;

/**
 * Class Processor
 * @package SoftCommerce\Rebound\Service\OrderExport
 */
class Processor extends AbstractService implements ProcessorInterface
{
    /**
     * @var ProcessorInterface[]
     */
    private array $processors;

    /**
     * Processor constructor.
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param Json $serializer
     * @param array $processors
     */
    public function __construct(
        DateTime $dateTime,
        Logger $logger,
        Json $serializer,
        array $processors = []
    ) {
        $this->processors = $processors;
        parent::__construct($dateTime, $logger, $serializer);
    }

    /**
     * @param OrderExport $context
     * @return Processor
     */
    public function init(OrderExport $context)
    {
        if ($entityFilter = $context->getEntityFilter()) {
            $this->processors = array_filter(
                $this->processors,
                function ($item) use ($entityFilter) {
                    return in_array($item, $entityFilter);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        foreach ($this->processors as $processor) {
            $processor->init($context);
        }

        return parent::init($context);
    }

    /**
     * @return $this|Processor
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->executeBefore();
        foreach ($this->processors as $entityType => $processor) {
            if (!$this->canProcess($entityType)) {
                continue;
            }

            try {
                $processor->execute();
            } catch (\Exception $e) {
                $this->getContext()
                    ->addResponse(
                        __(
                            '%1. [Order: %2, Entity: %3]',
                            $e->getMessage(),
                            $this->getContext()->getSalesOrder()->getIncrementId(),
                            $entityType
                        ),
                        Status::ERROR
                    )->addToClientOrder(
                        $entityType,
                        [
                            OrderExportInterface::STATUS => Status::ERROR,
                            OrderExportInterface::MESSAGE => $e->getMessage(),
                            OrderExportInterface::REQUEST_ENTRY => $this->serializer
                                ->serialize($this->getContext()->getRequest())
                        ]
                    );
            }
        }

        $this->executeAfter();
        return $this;
    }

    /**
     * @param string $entityType
     * @return bool
     */
    private function canProcess(string $entityType): bool
    {
        return !$this->getContext()->getEntityFilter()
            || in_array($entityType, $this->getContext()->getEntityFilter());
    }
}
