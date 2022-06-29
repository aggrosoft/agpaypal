<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class ShippingDetailName extends RequestObject
{
    /**
     * @var string
     */
    public $full_name;

    public function __construct($full_name)
    {
        $this->full_name = $full_name;
    }
}
