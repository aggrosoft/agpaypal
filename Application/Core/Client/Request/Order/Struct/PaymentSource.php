<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class PaymentSource extends RequestObject
{
    public const PAYPAL = 'PAYPAL';
    public const CARD = 'CARD';
    public const BANCONTACT = 'BANCONTACT';
    public const BLIK = 'BLIK';
    public const EPS = 'EPS';
    public const GIROPAY = 'GIROPAY';
    public const IDEAL = 'IDEAL';
    public const MYBANK = 'MYBANK';
    public const P24 = 'P24';
    public const SOFORT = 'SOFORT';
    public const TRUSTLY = 'TRUSTLY';
    public const PAY_UPON_INVOICE = 'PAY_UPON_INVOICE';
    public const SEPA = 'SEPA';
    public const PAY_LATER = 'PAY_LATER';

    /**
     * @var object
     */
    public $token;

    /**
     * @var object
     */
    public $bancontact;

    /**
     * @var object
     */
    public $blik;

    /**
     * @var object
     */
    public $eps;

    /**
     * @var object
     */
    public $giropay;

    /**
     * @var object
     */
    public $ideal;

    /**
     * @var object
     */
    public $mybank;

    /**
     * @var object
     */
    public $p24;

    /**
     * @var object
     */
    public $sofort;

    /**
     * @var object
     */
    public $trustly;

    /**
     * @var object
     */
    public $card;

    /**
     * @var object
     */
    public $pay_upon_invoice;

    public static function isPUI($paymentId = null)
    {
        return $paymentId === self::PAY_UPON_INVOICE;
    }

    public static function isAPM($paymentId = null)
    {
        return $paymentId && $paymentId !== PaymentSource::PAYPAL && $paymentId !== PaymentSource::CARD && $paymentId !== PaymentSource::PAY_LATER && $paymentId !== PaymentSource::SEPA && $paymentId !== PaymentSource::PAY_UPON_INVOICE;
    }

    public static function isValidFunding($paymentId) {
        return in_array($paymentId, [
            'card',
            'credit',
            'paylater',
            'bancontact',
            'blik',
            'eps',
            'giropay',
            'ideal',
            'mecadopago',
            'mybank',
            'p24',
            'sepa',
            'sofort',
            'venmo'
        ]);
    }
}
