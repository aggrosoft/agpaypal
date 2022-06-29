<?php

namespace Aggrosoft\PayPal\Application\Core\Factory\Request\Order;

use Aggrosoft\PayPal\Application\Core\Client\Request\Order\CapturePaymentRequest;

class CapturePaymentRequestFactory
{
    public static function create($orderId)
    {
        return new CapturePaymentRequest($orderId);
    }
}
