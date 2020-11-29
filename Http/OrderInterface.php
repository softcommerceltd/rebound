<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace SoftCommerce\Rebound\Http;

/**
 * Interface OrderInterface
 * @package SoftCommerce\Rebound\Http
 */
interface OrderInterface
{
    /**
     * Order meta data
     */
    const ORDER_ID = 'order_id';
    const ORDER_REFERENCE = 'order_reference';
    const ORDER_DATE = 'order_date';
    const CURRENCY_CODE = 'currency_code';
    const OVERWRITE_DATA = 'overwrite_data';
    const RMA = 'rma';
    const PHONE = 'phone';
    const EMAIL = 'email';
    const EXPORT_DATE = 'export_date';
    const EXPORT_AWB = 'export_awb';
    const EXPORT_CARRIER_NAME = 'export_carrier_name';
    const FREE_RETURN = 'free_return';
    const CUSTOM_FIELDS = 'custom_fields';
    const MESSAGE = 'message';

    /**
     * Address meta data
     */
    const CONTACT_NAME = 'contact_name';
    const COMPANY_NAME = 'company_name';
    const COUNTRY = 'country';
    const STATE = 'state';
    const ZIP = 'zip';
    const CITY = 'city';
    const SUBURB = 'suburb';
    const ADDR1 = 'addr1';
    const ADDR2 = 'addr2';
    const ADDR3 = 'addr3';
    const NEIGHBORHOOD = 'neighborhood';

    /**
     * Item meta data
     */
    const SKU_CODE = 'sku_code';
    const SKU_DESC = 'sku_desc';
    const QUANTITY = 'quantity';
    const PRICE = 'price';
    const WEIGHT = 'weight';
    const WEIGHT_UOM = 'weight_uom';
    const LENGTH = 'length';
    const WIDTH = 'width';
    const HEIGHT = 'height';
    const DIMENSIONS_UOM = 'dimensions_uom';
    const COUNTRY_CODE = 'country_code';
    const HS_CODE = 'hs_code';
    const IMG_PATH = 'img_path';
    const DANGEROUS_GOODS = 'dangerous_goods';
    const SKU_URL = 'sku_url';
    const TRACKING = 'tracking';
    const NON_RETURNABLE = 'non_returnable';
    const DAYS_FOR_RETURN = 'days_for_return';

    /**
     * Custom meta data
     */
    const FIELD1 = 'field1';
    const FIELD2 = 'field2';
    const FIELD3 = 'field3';
    const FIELD4 = 'field4';
    const FIELD5 = 'field5';
}
