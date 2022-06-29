<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class PaymentMethod extends RequestObject
{
    public const PAYEE_PREFERRED_UNRESTRICTED = 'UNRESTRICTED';
    public const PAYEE_PREFERRED_IMMEDIATE_PAYMENT = 'IMMEDIATE_PAYMENT_REQUIRED';

    public const SEC_TEL = 'TEL';
    public const SEC_WEB = 'WEB';
    public const SEC_CCD = 'CCD';
    public const SEC_PPD = 'PPD';

    /**
     * @var string
     */
    public $payee_preferred;

    /**
     * @var string
     */
    public $standard_entry_class_code;

    public function __construct($payee_preferred = self::PAYEE_PREFERRED_UNRESTRICTED, $standard_entry_class_code = self::SEC_WEB)
    {
        $this->payee_preferred = $payee_preferred;
        $this->standard_entry_class_code = $standard_entry_class_code;
    }
}
