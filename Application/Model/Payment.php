<?php

namespace Aggrosoft\PayPal\Application\Model;

use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;

class Payment extends Payment_parent
{
    /*
     * @see: https://developer.paypal.com/docs/checkout/apm/reference/method-icons/
     */
    public function getPayPalPaymentIcon() {
        switch($this->oxpayments__agpaypalpaymentmethod->value) {
            case PaymentSource::BANCONTACT:
                return 'https://www.paypalobjects.com/images/checkout/alternative_payments/paypal_bancontact_color.svg';
            case PaymentSource::BLIK:
                return 'https://www.paypalobjects.com/images/checkout/alternative_payments/paypal_blik_color.svg';
            case PaymentSource::EPS:
                return 'https://www.paypalobjects.com/images/checkout/alternative_payments/paypal_eps_color.svg';
            case PaymentSource::GIROPAY:
                return 'https://www.paypalobjects.com/images/checkout/alternative_payments/paypal_giropay_color.svg';
            case PaymentSource::IDEAL:
                return 'https://www.paypalobjects.com/images/checkout/alternative_payments/paypal_ideal_color.svg';
            case PaymentSource::MYBANK:
                return 'https://www.paypalobjects.com/images/checkout/alternative_payments/paypal_mybank_color.svg';
            case PaymentSource::P24:
                return 'https://www.paypalobjects.com/images/checkout/alternative_payments/paypal_przelewy24_color.svg';
            case PaymentSource::SOFORT:
                return 'https://www.paypalobjects.com/images/checkout/alternative_payments/paypal_sofort_black.svg';
            case PaymentSource::CARD:
                $viewConfig = oxNew(\OxidEsales\Eshop\Core\ViewConfig::class);
                return $viewConfig->getModuleUrl('agpaypal', 'out/image/credit-cards.svg');
            case PaymentSource::PAYPAL:
                $viewConfig = oxNew(\OxidEsales\Eshop\Core\ViewConfig::class);
                return $viewConfig->getModuleUrl('agpaypal', 'out/image/paypal.png');
            case PaymentSource::PAY_UPON_INVOICE:
                $viewConfig = oxNew(\OxidEsales\Eshop\Core\ViewConfig::class);
                return $viewConfig->getModuleUrl('agpaypal', 'out/image/ratepay_logo_open-invoice_whitelabel_with_logo_de_black.svg');

        }
    }
}