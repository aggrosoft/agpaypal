<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class AmountWithBreakdown extends RequestObject
{
    /** @var string */
    public $currency_code;
    /** @var string */
    public $value;
    /** @var AmountBreakdown */
    public $breakdown;

    /**
     * @param string $currency_code
     * @param string $value
     * @param AmountBreakdown|null $breakdown
     */
    public function __construct(string $currency_code, string $value, AmountBreakdown $breakdown = null)
    {
        $this->currency_code = $currency_code;
        $this->value = $value;
        $this->breakdown = $breakdown;
    }
}
