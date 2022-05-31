<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class Name extends RequestObject
{
    /**
     * @var string
     */
    public $prefix;

    /**
     * @var string
     */
    public $given_name;


    /**
     * @var string
     */
    public $surname;


    /**
     * @var string
     */
    public $middle_name;

    /**
     * @var string
     */
    public $suffix;

    /**
     * @var string
     */
    public $full_name;

    public function __construct ($given_name, $surname)
    {
        $this->given_name = $given_name;
        $this->surname = $surname;
    }

}