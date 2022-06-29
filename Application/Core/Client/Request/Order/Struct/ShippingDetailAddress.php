<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class ShippingDetailAddress extends RequestObject
{
    /** @var string */
    public $address_line_1;
    /** @var string */
    public $address_line_2;
    /** @var string */
    public $admin_area_2;
    /** @var string */
    public $admin_area_1;
    /** @var string */
    public $postal_code;
    /** @var string */
    public $country_code;
}
