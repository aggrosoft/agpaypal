<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order;

use Aggrosoft\PayPal\Application\Core\Client\Request\IPayPalRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class UpdateOrderInvoiceNumberRequest extends RequestObject implements IPayPalRequest
{
    /**
     * @var string
     */
    protected $orderId;

    /**
     * @var string
     */
    protected $invoiceNumber;

    public function __construct($orderId, $invoiceNumber)
    {
        $this->orderId = $orderId;
        $this->invoiceNumber = $invoiceNumber;
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
        return 'PATCH';
    }

    public function getBody()
    {
        return json_encode([[
            'op' => 'add',
            'path' => "/purchase_units/@reference_id=='default'/invoice_id",
            'value' => strval($this->invoiceNumber)
        ]]);
    }
}
