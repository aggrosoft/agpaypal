<?php

namespace Aggrosoft\PayPal\Application\Controller\Admin;

use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;

class PaymentMain extends PaymentMain_parent
{
    public function getPayPalPaymentMethods()
    {
        return [
            PaymentSource::PAYPAL,
            PaymentSource::PAY_UPON_INVOICE,
            PaymentSource::SOFORT,
            PaymentSource::CARD,
            PaymentSource::BANCONTACT,
            PaymentSource::BLIK,
            PaymentSource::EPS,
            PaymentSource::GIROPAY,
            PaymentSource::IDEAL,
            PaymentSource::MYBANK,
            PaymentSource::P24,
            PaymentSource::TRUSTLY,
        ];
    }
}
