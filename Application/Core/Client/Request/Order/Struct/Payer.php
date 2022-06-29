<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class Payer extends RequestObject
{
    /**
     * @var string
     */
    public $email_address;

    /**
     * @var string
     */
    public $payer_id;

    /**
     * @var object
     */
    public $name;

    /**
     * @var object
     */
    public $phone;

    /**
     * @var string
     */
    public $birth_date;

    /**
     * @var object
     */
    public $tax_info;

    /**
     * @var AddressPortable
     */
    public $address;

    /**
     * @return string
     */
    public function getEmailAddress(): string
    {
        return $this->email_address;
    }

    /**
     * @param string $email_address
     */
    public function setEmailAddress(string $email_address)
    {
        $this->email_address = $email_address;
    }

    /**
     * @return string
     */
    public function getPayerId(): string
    {
        return $this->payer_id;
    }

    /**
     * @param string $payer_id
     */
    public function setPayerId(string $payer_id)
    {
        $this->payer_id = $payer_id;
    }

    /**
     * @return object
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param object $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return object
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param object $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getBirthDate(): string
    {
        return $this->birth_date;
    }

    /**
     * @param string $birth_date
     */
    public function setBirthDate(string $birth_date)
    {
        $this->birth_date = $birth_date;
    }

    /**
     * @return object
     */
    public function getTaxInfo()
    {
        return $this->tax_info;
    }

    /**
     * @param object $tax_info
     */
    public function setTaxInfo($tax_info)
    {
        $this->tax_info = $tax_info;
    }

    /**
     * @return AddressPortable
     */
    public function getAddress(): AddressPortable
    {
        return $this->address;
    }

    /**
     * @param AddressPortable $address
     */
    public function setAddress(AddressPortable $address)
    {
        $this->address = $address;
    }
}
