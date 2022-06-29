<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order;

use Aggrosoft\PayPal\Application\Core\Client\Request\IPayPalRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\JSONBodyTrait;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;
use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class CapturePaymentRequest extends RequestObject implements IPayPalRequest
{
    use JSONBodyTrait;

    /**
     * @var string
     */
    protected $orderId;

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @var PaymentSource
     */
    public $payment_source;

    public function getEndpoint()
    {
        return 'v2/checkout/orders/'.$this->orderId.'/capture';
    }

    public function getHeaders()
    {
        return ['Prefer' => 'return=representation'];
    }

    public function getMethod()
    {
        return 'POST';
    }

    /**
     * @return PaymentSource
     */
    public function getPaymentSource(): PaymentSource
    {
        return $this->payment_source;
    }

    /**
     * @param PaymentSource $payment_source
     */
    public function setPaymentSource(PaymentSource $payment_source = null)
    {
        $this->payment_source = $payment_source;
    }
}
