<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class Money extends RequestObject
{
    public function __construct($currency_code = null, $value = null)
    {
        $this->currency_code = $currency_code;
        $this->value = (string)round($value, 2);
    }

    /** @var string */
    public $currency_code;
    /** @var string */
    public $value;
}
