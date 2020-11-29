<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Model;

/**
 * Interface ConfigInterface
 * @package SoftCommerce\Rebound\Model
 */
interface ConfigInterface
{
    const CONFIG_ENTITY = 'entity';
    const XML_PATH_CLIENT_API_NAME = 'softcommerce_rebound/client/api_name';
    const XML_PATH_CLIENT_API_URL = 'softcommerce_rebound/client/api_url';
    const XML_PATH_CLIENT_API_RETRY = 'softcommerce_rebound/client/api_retry';
    const XML_PATH_CLIENT_API_CONNECTION_TIMEOUT = 'softcommerce_rebound/client/api_connection_timeout';
    const XML_PATH_CLIENT_API_TIMEOUT = 'softcommerce_rebound/client/api_timeout';
    // Order export config path
    const XML_PATH_ORDER_EXPORT_ENTITY = 'softcommerce_rebound/order_export_';
    const XML_PATH_ORDER_EXPORT_IS_ACTIVE = '/is_active';
    const XML_PATH_ORDER_EXPORT_IS_SANDBOX = '/is_sandbox';
    const XML_PATH_CLIENT_API_USERNAME = '/api_username';
    const XML_PATH_CLIENT_API_USERNAME_SANDBOX = '/api_username_sandbox';
    const XML_PATH_CLIENT_API_ACCESS_TOKEN = '/api_access_token';
    const XML_PATH_CLIENT_API_ACCESS_TOKEN_SANDBOX = '/api_access_token_sandbox';
    // DEV config path
    const XML_PATH_DEV_IS_ACTIVE_DEBUG = 'softcommerce_rebound/dev/is_active_debug';
    const XML_PATH_DEV_DEBUG_PRINT_TO_ARRAY = 'softcommerce_rebound/dev/debug_print_to_array';

    /**
     * @return bool
     */
    public function getIsActive(): bool;

    /**
     * @return bool
     */
    public function getIsSandbox(): bool;

    /**
     * @return string
     */
    public function getClientUsername(): string;

    /**
     * @return string
     */
    public function getClientAccessToken(): string;

    /**
     * @return string
     */
    public function getClientName(): string;

    /**
     * @param null $route
     * @return string
     */
    public function getClientUrl($route = null): string;

    /**
     * @return int
     */
    public function getClientRetries(): int;

    /**
     * @return int
     */
    public function getClientConnectionTimeout(): int;

    /**
     * @return int
     */
    public function getClientTimeout(): int;

    /**
     * @return string
     */
    public function getClientAuthUrl(): string;

    /**
     * @return string
     */
    public function getClientOrderUrl(): string;

    /**
     * @return string
     */
    public function getClientOrderCreateUrl(): string;

    /**
     * @return string
     */
    public function getClientOrderDeleteUrl(): string;

    /**
     * @return string
     */
    public function getClientOrderSearchUrl(): string;

    /**
     * @return string
     */
    public function getClientReturnUrl(): string;

    /**
     * @return string
     */
    public function getClientReturnsUrl(): string;

    /**
     * @return string
     */
    public function getClientReturnCreateUrl(): string;

    /**
     * @return string
     */
    public function getClientReturnSearchUrl(): string;

    /**
     * @return string
     */
    public function getClientReturnCancelUrl(): string;

    /**
     * @return string
     */
    public function getClientReturnBillingUrl(): string;

    /**
     * @return string
     */
    public function getClientRequestPriceUrl(): string;

    /**
     * @return string
     */
    public function getClientTrackingByDateUrl(): string;

    /**
     * @return string
     */
    public function getClientTrackingByDateRangeUrl(): string;

    /**
     * @return string
     */
    public function getClientTrackingAddStatusUrl(): string;

    /**
     * @return string
     */
    public function getClientDropOffPointsUrl(): string;

    /**
     * @return string
     */
    public function getClientCheckAvailabilityUrl(): string;

    /**
     * @return mixed
     */
    public function getIsActiveDebug(): bool;

    /**
     * @return bool
     */
    public function getIsDebugPrintToArray(): bool;
}
