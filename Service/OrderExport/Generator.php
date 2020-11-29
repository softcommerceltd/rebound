<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Service\OrderExport;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order\Item as SalesOrderItem;
use SoftCommerce\Rebound\Http\OrderInterface as ClientOrderInterface;
use SoftCommerce\Rebound\Logger\Logger;

/**
 * Class Generator
 * @package SoftCommerce\Rebound\Service\OrderExport
 */
class Generator extends AbstractService implements ProcessorInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var Configurable
     */
    private Configurable $configurable;

    /**
     * @var ImageFactory
     */
    protected ImageFactory $productImageFactory;

    /**
     * Generator constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurable
     * @param ImageFactory $productImageFactory
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param Json $serializer
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Configurable $configurable,
        ImageFactory $productImageFactory,
        DateTime $dateTime,
        Logger $logger,
        Json $serializer
    ) {
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
        $this->productImageFactory = $productImageFactory;
        parent::__construct($dateTime, $logger, $serializer);
    }

    /**
     * @return $this|Generator
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->executeBefore()
            ->buildOrderData()
            ->buildItemData();
        return $this;
    }

    /**
     * @return Generator
     */
    public function executeAfter()
    {
        $this->getContext()->setRequest($this->getRequest() ?: []);
        return parent::executeAfter();
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    private function buildOrderData()
    {
        $salesOrder = $this->getContext()->getSalesOrder();
        if (!$address = $salesOrder->getShippingAddress()) {
            if (!$address = $salesOrder->getBillingAddress()) {
                throw new LocalizedException(
                    __('Could not get order address. Order: %1', $salesOrder->getIncrementId())
                );
            }
        }

        $tracks = $salesOrder->getTracksCollection()->getData();
        $trackNo = implode(', ', array_column($tracks, 'track_number') ?: []);
        $trackCourier = $salesOrder->getShippingService()
            ?: (implode(', ', array_column($tracks, 'title') ?: []) ?: $salesOrder->getShippingMethod());

        $this->getContext()->setRequest(
            [
                ClientOrderInterface::ORDER_REFERENCE => $salesOrder->getIncrementId(),
                ClientOrderInterface::ORDER_DATE => $salesOrder->getCreatedAt(),
                ClientOrderInterface::CONTACT_NAME => $salesOrder->getCustomerName(),
                ClientOrderInterface::COMPANY_NAME => $address->getCompany(),
                ClientOrderInterface::ADDR1 => $address->getStreetLine(1),
                ClientOrderInterface::ADDR2 => $address->getStreetLine(2),
                ClientOrderInterface::ADDR3 => $address->getStreetLine(3),
                ClientOrderInterface::CITY => $address->getCity(),
                ClientOrderInterface::ZIP => $address->getPostcode(),
                ClientOrderInterface::STATE => $address->getRegionCode(),
                ClientOrderInterface::COUNTRY => $address->getCountryId(),
                ClientOrderInterface::PHONE => $address->getTelephone(),
                ClientOrderInterface::EMAIL => $salesOrder->getCustomerEmail(),
                ClientOrderInterface::CURRENCY_CODE => $salesOrder->getOrderCurrencyCode(),
                ClientOrderInterface::EXPORT_AWB => $trackNo,
                ClientOrderInterface::EXPORT_CARRIER_NAME => $trackCourier,
                ClientOrderInterface::FREE_RETURN => $salesOrder->getIsFreeReturn(),
                ClientOrderInterface::CUSTOM_FIELDS => [
                    ClientOrderInterface::FIELD1 => $salesOrder->getId(),
                    ClientOrderInterface::FIELD2 => $salesOrder->getStoreId(),
                    ClientOrderInterface::FIELD3 => $salesOrder->getShippingDescription(),
                    ClientOrderInterface::FIELD4 => $salesOrder->getShippingAmount(),
                    ClientOrderInterface::FIELD5 => $salesOrder->getWeight()
                ],
            ]
        );

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    private function buildItemData()
    {
        $salesOrder = $this->getContext()->getSalesOrder();
        if (!$address = $salesOrder->getShippingAddress()) {
            if (!$address = $salesOrder->getBillingAddress()) {
                throw new LocalizedException(
                    __('Could not get order address. Order: %1', $salesOrder->getIncrementId())
                );
            }
        }

        $request = [];
        /** @var SalesOrderItem $item */
        foreach ($salesOrder->getAllVisibleItems() as $item) {
            $product = $this->getProduct($item);
            $request[] = [
                ClientOrderInterface::SKU_CODE => $item->getSku(),
                ClientOrderInterface::SKU_DESC => $item->getName(),
                ClientOrderInterface::QUANTITY => $item->getQtyOrdered(),
                ClientOrderInterface::PRICE => $item->getPriceInclTax(),
                ClientOrderInterface::WEIGHT => ($item->getWeight() / 1000),
                ClientOrderInterface::LENGTH => $item->getLength(),
                ClientOrderInterface::WIDTH => $item->getWidth(),
                ClientOrderInterface::HEIGHT => $item->getHeight(),
                ClientOrderInterface::DIMENSIONS_UOM => 'mm',
                ClientOrderInterface::HS_CODE => $product->getData('commodity_code'),
                ClientOrderInterface::COUNTRY_CODE => $address->getCountryId(),
                ClientOrderInterface::DANGEROUS_GOODS => 'No',
                ClientOrderInterface::EXPORT_DATE => $this->dateTime->gmtDate(),
                // ClientOrderInterface::EXPORT_AWB => $tracks,
                // ClientOrderInterface::TRACKING => $tracks,
                ClientOrderInterface::DAYS_FOR_RETURN => 365,
                ClientOrderInterface::SKU_URL => null !== $product ? $product->getProductUrl() : '',
                ClientOrderInterface::IMG_PATH => null !== $product
                    ? (string) $this->productImageFactory
                        ->create()
                        ->init($product, 'product_page_image_large')
                        ->setImageFile($product->getImage())
                        ->getUrl()
                    : '',
                ClientOrderInterface::CUSTOM_FIELDS => []
            ];
        }

        $this->getContext()->setRequest($request, 'item');

        return $this;
    }

    /**
     * @param SalesOrderItem $item
     * @return ProductInterface|Product|null
     * @throws NoSuchEntityException
     */
    private function getProduct(SalesOrderItem $item)
    {
        if ($item->getParentItem()) {
            return $item->getParentItem()->getProduct();
        }

        if (!$product = $item->getProduct()) {
            $product = $this->productRepository->getById($item->getProductId());
        }

        if ($product->isVisibleInSiteVisibility()) {
            return $product;
        }

        $configurableProductIds = $this->configurable->getParentIdsByChild($product->getId());
        if (!$configurableProductId = current($configurableProductIds)) {
            return $product;
        }

        try {
            $product = $this->productRepository->getById($configurableProductId);
        } catch (\Exception $e) {
            $product = $item->getProduct();
        }

        return $product;
    }
}
