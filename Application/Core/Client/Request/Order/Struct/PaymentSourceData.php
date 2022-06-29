<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class PaymentSourceData extends RequestObject
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $country_code;

    public function __construct($name, $country_code)
    {
        $this->name = $name;
        $this->country_code = $country_code;
    }
}
