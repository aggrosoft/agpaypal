<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class AmountBreakdown extends RequestObject
{
    /** @var Money */
    public $item_total;
    /** @var Money */
    public $shipping;
    /** @var Money */
    public $handling;
    /** @var Money */
    public $tax_total;
    /** @var Money */
    public $insurance;
    /** @var Money */
    public $shipping_discount;
    /** @var Money */
    public $discount;
}
