<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order;

use Aggrosoft\PayPal\Application\Core\Client\Request\IPayPalRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class GetOrderRequest extends RequestObject implements IPayPalRequest
{
    /**
     * @var string
     */
    protected $orderId;

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function getEndpoint()
    {
        return 'v2/checkout/orders/'.$this->orderId;
    }

    public function getHeaders()
    {
        return [];
    }

    public function getMethod()
    {
        return 'GET';
    }

    public function getBody()
    {
        return '';
    }
}
