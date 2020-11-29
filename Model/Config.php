<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use SoftCommerce\Rebound\Model\Source\OrderEntityType;

/**
 * Class Config
 * @package SoftCommerce\Shipping\Model
 */
class Config implements ConfigInterface
{
    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var string|null
     */
    private ?string $entity;

    /**
     * Config constructor.
     * @param EncryptorInterface $encryptor
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        EncryptorInterface $encryptor,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->entity = isset($data[self::CONFIG_ENTITY])
            ? self::XML_PATH_ORDER_EXPORT_ENTITY . $data[self::CONFIG_ENTITY]
            : self::XML_PATH_ORDER_EXPORT_ENTITY . OrderEntityType::RETURNS;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return (bool) $this->getConfig($this->entity . self::XML_PATH_ORDER_EXPORT_IS_ACTIVE);
    }

    /**
     * @return bool
     */
    public function getIsSandbox(): bool
    {
        return (bool) $this->getConfig($this->entity . self::XML_PATH_ORDER_EXPORT_IS_SANDBOX);
    }

    /**
     * @return string
     */
    public function getClientUsername(): string
    {
        return (string) $this->getConfig(
            $this->getIsSandbox()
                ? $this->entity . self::XML_PATH_CLIENT_API_USERNAME_SANDBOX
                : $this->entity . self::XML_PATH_CLIENT_API_USERNAME
        );
    }

    /**
     * @return string
     */
    public function getClientAccessToken(): string
    {
        $value = $this->getConfig(
            $this->getIsSandbox()
                ? $this->entity . self::XML_PATH_CLIENT_API_ACCESS_TOKEN_SANDBOX
                : $this->entity . self::XML_PATH_CLIENT_API_ACCESS_TOKEN
        );

        return $this->encryptor->decrypt($value);
    }

    /**
     * @return string
     */
    public function getClientName(): string
    {
        return (string) $this->getConfig(self::XML_PATH_CLIENT_API_NAME);
    }

    /**
     * @param null $route
     * @return string
     */
    public function getClientUrl($route = null): string
    {
        return (string) $this->getConfig(self::XML_PATH_CLIENT_API_URL) . $route;
    }

    /**
     * @return int
     */
    public function getClientRetries(): int
    {
        return (int) $this->getConfig(self::XML_PATH_CLIENT_API_RETRY);
    }

    /**
     * @return int
     */
    public function getClientConnectionTimeout(): int
    {
        return (int) $this->getConfig(self::XML_PATH_CLIENT_API_CONNECTION_TIMEOUT);
    }

    /**
     * @return int
     */
    public function getClientTimeout(): int
    {
        return (int) $this->getConfig(self::XML_PATH_CLIENT_API_TIMEOUT);
    }

    /**
     * @return string
     */
    public function getClientAuthUrl(): string
    {
        return (string) $this->getClientUrl('/api/Auth');
    }

    /**
     * @return string
     */
    public function getClientOrderUrl(): string
    {
        return (string) $this->getClientUrl('/api/orders/get/json');
    }

    /**
     * @return string
     */
    public function getClientOrderCreateUrl(): string
    {
        return (string) $this->getClientUrl('/api/orders/create/json');
    }

    /**
     * @return string
     */
    public function getClientOrderDeleteUrl(): string
    {
        return (string) $this->getClientUrl('/api/orders/delete/json');
    }

    /**
     * @return string
     */
    public function getClientOrderSearchUrl(): string
    {
        return (string) $this->getClientUrl('/api/orders/search/json');
    }

    /**
     * @return string
     */
    public function getClientReturnUrl(): string
    {
        return (string) $this->getClientUrl('/api/returns/get/json');
    }

    /**
     * @return string
     */
    public function getClientReturnsUrl(): string
    {
        return (string) $this->getClientUrl('/api/returns/items/json');
    }

    /**
     * @return string
     */
    public function getClientReturnCreateUrl(): string
    {
        return (string) $this->getClientUrl('/api/returns/create/json');
    }

    /**
     * @return string
     */
    public function getClientReturnSearchUrl(): string
    {
        return (string) $this->getClientUrl('/api/returns/search/json');
    }

    /**
     * @return string
     */
    public function getClientReturnCancelUrl(): string
    {
        return (string) $this->getClientUrl('/api/returns/cancel/json');
    }

    /**
     * @return string
     */
    public function getClientReturnBillingUrl(): string
    {
        return (string) $this->getClientUrl('/api/returns/billing/json');
    }

    /**
     * @return string
     */
    public function getClientRequestPriceUrl(): string
    {
        return (string) $this->getClientUrl('/api/returns/request_for_prices/json');
    }

    /**
     * @return string
     */
    public function getClientTrackingByDateUrl(): string
    {
        return (string) $this->getClientUrl('/api/tracking/get_by_date_range/json');
    }

    /**
     * @return string
     */
    public function getClientTrackingByDateRangeUrl(): string
    {
        return (string) $this->getClientUrl('/api/tracking/get_by_date_added_range/json');
    }

    /**
     * @return string
     */
    public function getClientTrackingAddStatusUrl(): string
    {
        return (string) $this->getClientUrl('/api/tracking/add_status/json');
    }

    /**
     * @return string
     */
    public function getClientDropOffPointsUrl(): string
    {
        return (string) $this->getClientUrl('/api/services/get_drop_off_points/json');
    }

    /**
     * @return string
     */
    public function getClientCheckAvailabilityUrl(): string
    {
        return (string) $this->getClientUrl('/api/check/available/json');
    }

    /**
     * @return mixed
     */
    public function getIsActiveDebug(): bool
    {
        return (bool) $this->scopeConfig->isSetFlag(self::XML_PATH_DEV_IS_ACTIVE_DEBUG);
    }

    /**
     * @return bool
     */
    public function getIsDebugPrintToArray(): bool
    {
        return (bool) $this->scopeConfig->isSetFlag(self::XML_PATH_DEV_DEBUG_PRINT_TO_ARRAY);
    }

    /**
     * @param $path
     * @return mixed
     */
    private function getConfig($path)
    {
        return $this->scopeConfig->getValue($path);
    }
}
