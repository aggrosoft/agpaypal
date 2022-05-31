<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class PaymentSource extends RequestObject
{
    const PAYPAL = 'PAYPAL';
    const CARD = 'CARD';
    const BANCONTACT = 'BANCONTACT';
    const BLIK = 'BLIK';
    const EPS = 'EPS';
    const GIROPAY = 'GIROPAY';
    const IDEAL = 'IDEAL';
    const MYBANK = 'MYBANK';
    const P24 = 'P24';
    const SOFORT = 'SOFORT';
    const TRUSTLY = 'TRUSTLY';
    const PAY_UPON_INVOICE = 'PAY_UPON_INVOICE';

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

}