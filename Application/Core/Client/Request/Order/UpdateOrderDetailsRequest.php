<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order;

use Aggrosoft\PayPal\Application\Core\Client\Request\IPayPalRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PurchaseUnitRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class UpdateOrderDetailsRequest extends RequestObject implements IPayPalRequest
{
    /**
     * @var string
     */
    protected $orderId;

    /**
     * @var string
     */
    protected $invoiceNumber;

    /**
     * @var PurchaseUnitRequest
     */
    protected $purchaseUnits;

    public function __construct($orderId, $invoiceNumber, $purchaseUnits)
    {
        $this->orderId = $orderId;
        $this->invoiceNumber = $invoiceNumber;
        $this->purchaseUnits = $purchaseUnits;
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
        return json_encode([
            [
                'op' => 'replace',
                'path' => "/purchase_units/@reference_id=='default'",
                'value' => $this->purchaseUnits
            ],
            [
                'op' => 'add',
                'path' => "/purchase_units/@reference_id=='default'/invoice_id",
                'value' => strval($this->invoiceNumber)
            ]
        ]);
    }
}
