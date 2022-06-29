<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class StoredPaymentSource extends RequestObject
{
    public const PAYMENT_INITIATOR_MERCHANT = 'MERCHANT';
    public const PAYMENT_INITIATOR_CUSTOMER = 'CUSTOMER';

    public const PAYMENT_TYPE_UNSCHEDULED = 'UNSCHEDULED';
    public const PAYMENT_TYPE_RECURRING = 'RECURRING';
    public const PAYMENT_TYPE_ONE_TIME = 'ONE_TIME';

    public const USAGE_FIRST = 'FIRST';
    public const USAGE_SUBSEQUENT = 'SUBSEQUENT';
    public const USAGE_DERIVED = 'DERIVED';

    /**
     * @var string
     */
    public $payment_initiator = self::PAYMENT_INITIATOR_CUSTOMER;

    /**
     * @var string
     */
    public $payment_type = self::PAYMENT_TYPE_ONE_TIME;

    /**
     * @var string
     */
    public $usage = self::USAGE_DERIVED;

    /**
     * @var string
     */
    public $previous_transaction_reference;
}
