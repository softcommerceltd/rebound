<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Http;

/**
 * Interface ReboundServerInterface
 * @package SoftCommerce\Rebound\Http
 */
interface ReboundServerInterface
{
    /**
     * HTTP Response Codes
     */
    const HTTP_CODE_OK                              = 200;
    const HTTP_CODE_UNAUTHORIZED                    = 401;
    const HTTP_CODE_ERROR_GEO_SESSION_HEADER        = 403;
    const HTTP_CODE_NOT_FOUND                       = 404;
    const HTTP_CODE_NOT_ACCEPTABLE                  = 406;
    const HTTP_CODE_INTERNAL_ERROR                  = 500;
    const HTTP_CODE_OUT_OF_SERVICE                  = 503;

    /**
     * HTTP Rate Limits
     */
    const HTTP_RATE_LIMIT_TIME_IN_SECONDS           = 60;
    const HTTP_RATE_LIMIT_CALLS_LIMIT               = 150;
}
