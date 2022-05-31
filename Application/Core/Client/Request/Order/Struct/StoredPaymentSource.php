<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class StoredPaymentSource extends RequestObject
{

    const PAYMENT_INITIATOR_MERCHANT = 'MERCHANT';
    const PAYMENT_INITIATOR_CUSTOMER = 'CUSTOMER';

    const PAYMENT_TYPE_UNSCHEDULED = 'UNSCHEDULED';
    const PAYMENT_TYPE_RECURRING = 'RECURRING';
    const PAYMENT_TYPE_ONE_TIME = 'ONE_TIME';

    const USAGE_FIRST = 'FIRST';
    const USAGE_SUBSEQUENT = 'SUBSEQUENT';
    const USAGE_DERIVED = 'DERIVED';

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