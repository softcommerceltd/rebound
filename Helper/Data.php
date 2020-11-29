<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package SoftCommerce\Rebound\Helper
 * @deprecared
 */
class Data extends AbstractHelper
{
    const XML_PATH_STORE_NAME                       = 'general/store_information/name';

    const XML_PATH_CLIENT_API_IS_SANDBOX            = 'softcommerce_rebound/client/is_sandbox';
    const XML_PATH_CLIENT_API_NAME                  = 'softcommerce_rebound/client/api_name';
    const XML_PATH_CLIENT_API_URL                   = 'softcommerce_rebound/client/api_url';
    const XML_PATH_CLIENT_API_URL_SANDBOX           = 'softcommerce_rebound/client/api_url_sandbox';
    const XML_PATH_CLIENT_API_USERNAME              = 'softcommerce_rebound/client/api_username';
    const XML_PATH_CLIENT_API_USERNAME_SANDBOX      = 'softcommerce_rebound/client/api_username_sandbox';
    const XML_PATH_CLIENT_API_PASSWORD              = 'softcommerce_rebound/client/api_password';
    const XML_PATH_CLIENT_API_PASSWORD_SANDBOX      = 'softcommerce_rebound/client/api_password_sandbox';
    const XML_PATH_CLIENT_API_ACCESS_TOKEN          = 'softcommerce_rebound/client/api_access_token';
    const XML_PATH_CLIENT_API_ACCESS_TOKEN_SANDBOX  = 'softcommerce_rebound/client/api_access_token_sandbox';
    const XML_PATH_CLIENT_API_RETRY                 = 'softcommerce_rebound/client/api_retry';
    const XML_PATH_CLIENT_API_CONNECTION_TIMEOUT    = 'softcommerce_rebound/client/api_connection_timeout';
    const XML_PATH_CLIENT_API_TIMEOUT               = 'softcommerce_rebound/client/api_timeout';

    const XML_PATH_ORDER_EXPORT_IS_ACTIVE             = 'softcommerce_rebound/order_export/is_active';

    const XML_PATH_DEV_IS_ACTIVE_DEBUG              = 'softcommerce_rebound/dev/is_active_debug';
    const XML_PATH_DEV_DEBUG_PRINT_TO_ARRAY         = 'softcommerce_rebound/dev/debug_print_to_array';

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var EncryptorInterface
     */
    private $_encryptor;

    /**
     * @var DateTime
     */
    private $_dateTime;

    /**
     * @var TimezoneInterface
     */
    private $_timezone;

    /**
     * Data constructor.
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param StoreManagerInterface $storeManager
     * @param DateTime $date
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        StoreManagerInterface $storeManager,
        DateTime $date,
        TimezoneInterface $timezone
    ) {
        $this->_storeManager = $storeManager;
        $this->_encryptor = $encryptor;
        $this->_dateTime = $date;
        $this->_timezone = $timezone;
        parent::__construct($context);
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    protected function _getStore()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @param $path
     * @param null $store
     * @return mixed
     * @throws NoSuchEntityException
     */
    protected function _getConfig($path, $store = null)
    {
        if (null === $store) {
            $store = $this->_getStore();
        }

        return $this->scopeConfig
            ->getValue($path, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null $store
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getStoreName($store = null)
    {
        return $this->_getConfig(self::XML_PATH_STORE_NAME, $store);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getIsSandbox()
    {
        return (bool) $this->_getConfig(self::XML_PATH_CLIENT_API_IS_SANDBOX);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getIsActiveOrderExport()
    {
        return (bool) $this->_getConfig(self::XML_PATH_ORDER_EXPORT_IS_ACTIVE);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientName()
    {
        return (string) $this->_getConfig(self::XML_PATH_CLIENT_API_NAME);
    }

    /**
     * @param null $route
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientUrl($route = null)
    {
        return (string) $this->_getConfig(
            $this->getIsSandbox()
                    ? self::XML_PATH_CLIENT_API_URL_SANDBOX
                    : self::XML_PATH_CLIENT_API_URL
        ) . $route;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientUsername()
    {
        return (string) $this->_getConfig(
            $this->getIsSandbox()
                ? self::XML_PATH_CLIENT_API_USERNAME_SANDBOX
                : self::XML_PATH_CLIENT_API_USERNAME
        );
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientPassword()
    {
        $pass = $this->_getConfig(
            $this->getIsSandbox()
                ? self::XML_PATH_CLIENT_API_PASSWORD_SANDBOX
                : self::XML_PATH_CLIENT_API_PASSWORD
        );

        return $this->_encryptor->decrypt($pass);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientAccessToken()
    {
        $value = $this->_getConfig(
            $this->getIsSandbox()
                ? self::XML_PATH_CLIENT_API_ACCESS_TOKEN_SANDBOX
                : self::XML_PATH_CLIENT_API_ACCESS_TOKEN
        );

        return $this->_encryptor->decrypt($value);
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getClientRetries()
    {
        return (int) $this->_getConfig(self::XML_PATH_CLIENT_API_RETRY);
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getClientConnectionTimeout()
    {
        return (int) $this->_getConfig(self::XML_PATH_CLIENT_API_CONNECTION_TIMEOUT);
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getClientTimeout()
    {
        return (int) $this->_getConfig(self::XML_PATH_CLIENT_API_TIMEOUT);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientAuthUrl()
    {
        return (string) $this->getClientUrl('/api/Auth');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientOrderUrl()
    {
        return (string) $this->getClientUrl('/api/orders/get/json');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientOrderCreateUrl()
    {
        return (string) $this->getClientUrl('/api/orders/create/json');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientOrderDeleteUrl()
    {
        return (string) $this->getClientUrl('/api/orders/delete/json');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientOrderSearchUrl()
    {
        return (string) $this->getClientUrl('/api/orders/search/json');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientReturnUrl()
    {
        return (string) $this->getClientUrl('/api/returns/get/json');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientReturnsUrl()
    {
        return (string) $this->getClientUrl('/api/returns/items/json');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientReturnCreateUrl()
    {
        return (string) $this->getClientUrl('/api/returns/create/json');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientReturnSearchUrl()
    {
        return (string) $this->getClientUrl('/api/returns/search/json');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientReturnCancelUrl()
    {
        return (string) $this->getClientUrl('/api/returns/cancel/json');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientReturnBillingUrl()
    {
        return (string) $this->getClientUrl('/api/returns/billing/json');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientRequestPriceUrl()
    {
        return (string) $this->getClientUrl('/api/returns/request_for_prices/json');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientTrackingByDateUrl()
    {
        return (string) $this->getClientUrl('/api/tracking/get_by_date_range/json');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientTrackingByDateRangeUrl()
    {
        return (string) $this->getClientUrl('/api/tracking/get_by_date_added_range/json');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientTrackingAddStatusUrl()
    {
        return (string) $this->getClientUrl('/api/tracking/add_status/json');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientDropOffPointsUrl()
    {
        return (string) $this->getClientUrl('/api/services/get_drop_off_points/json');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getClientCheckAvailabilityUrl()
    {
        return (string) $this->getClientUrl('/api/check/available/json');
    }

    /**
     * @return mixed
     */
    public function getIsActiveDebug()
    {
        return (bool) $this->scopeConfig->isSetFlag(self::XML_PATH_DEV_IS_ACTIVE_DEBUG);
    }

    /**
     * @return bool
     */
    public function getIsDebugPrintToArray()
    {
        return (bool) $this->scopeConfig->isSetFlag(self::XML_PATH_DEV_DEBUG_PRINT_TO_ARRAY);
    }

    /**
     * @param $input
     * @return string|null
     * @throws \Exception
     */
    public function getDateTimeLocale($input)
    {
        if (!$input) {
            return null;
        } elseif (is_numeric($input)) {
            $result = $this->_dateTime->gmtDate(null, $input);
        } else {
            $result = $input;
        }

        $dateTime = (new \DateTime($result))
            ->setTimezone(new \DateTimeZone($this->scopeConfig->getValue('general/locale/timezone')));
        $result = $dateTime->format(\DateTime::W3C);

        return $result;
    }
}
