<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class Money extends RequestObject
{
    public function __construct($currency_code = null, $value = null)
    {
        $this->currency_code = $currency_code;
        $this->value = round($value, 2);
    }

    public $currency_code;
    public $value;
}
