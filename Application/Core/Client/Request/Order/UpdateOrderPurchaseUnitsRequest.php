<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order;

use Aggrosoft\PayPal\Application\Core\Client\Request\IPayPalRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\JSONBodyTrait;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\ApplicationContext;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\Payer;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PurchaseUnitRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class UpdateOrderPurchaseUnitsRequest extends RequestObject implements IPayPalRequest
{

    /**
     * @var string
     */
    protected $orderId;

    /**
     * @var PurchaseUnitRequest
     */
    protected $purchaseUnits;

    public function __construct ($orderId, $purchaseUnits)
    {
        $this->orderId = $orderId;
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
                'path' => "/purchase_units/@reference_id=='default'/amount",
                'value' => $this->purchaseUnits->amount
            ],
            [
                'op' => 'replace',
                'path' => "/purchase_units/@reference_id=='default'/shipping/options",
                'value' => $this->purchaseUnits->shipping->options
            ]
        ]);
    }

}