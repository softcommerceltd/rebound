<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);
namespace SoftCommerce\Rebound\Ui\Component\Listing\Column;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Status
 */
class OrderId extends Column
{
    /**
     * @var Json
     */
    private Json $serializer;

    /**
     * OrderId constructor.
     * @param Json $serializer
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Json $serializer,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->serializer = $serializer;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as & $item) {
            if (!isset($item[$this->getData('name')])) {
                continue;
            }

            $result = null;
            try {
                $value = $this->serializer->unserialize($item[$this->getData('name')]);
                foreach ($value as $entity => $id) {
                    $result .= "$entity: $id\n";
                }
            } catch (\InvalidArgumentException $e) {
                $result = $e->getMessage();
            }

            $item[$this->getData('name')] = $result ?: $item[$this->getData('name')];
        }

        return $dataSource;
    }
}
