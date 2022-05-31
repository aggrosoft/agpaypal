<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class Payee extends RequestObject
{
    public $email_address;
    public $merchant_id;

    public function __construct($email_address, $merchant_id = null)
    {
        $this->email_address = $email_address;
        $this->merchant_id = $merchant_id;
    }
}