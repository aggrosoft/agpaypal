<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class Item extends RequestObject
{
    /** @var string */
    public $name;
    /** @var Money */
    public $unit_amount;
    /** @var Money */
    public $tax;
    /** @var string */
    public $quantity;
    /** @var string */
    public $description;
    /** @var string */
    public $sku;
    /** @var string */
    public $category;
    /** @var float */
    public $tax_rate;
}