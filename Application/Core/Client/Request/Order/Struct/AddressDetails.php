<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class AddressDetails extends RequestObject
{
    /**
     * @var string
     */
    public $street_number;

    /**
     * @var string
     */
    public $street_name;

    /**
     * @var string
     */
    public $street_type;

    /**
     * @var string
     */
    public $delivery_service;

    /**
     * @var string
     */
    public $building_name;

    /**
     * @var string
     */
    public $sub_building;
}
