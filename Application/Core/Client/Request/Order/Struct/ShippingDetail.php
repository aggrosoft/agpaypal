<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class ShippingDetail extends RequestObject
{
    /** @var ShippingDetailName */
    public $name;
    /** @var string */
    public $type;
    /** @var ShippingDetailAddress */
    public $address;
    /** @var array<ShippingDetailOption> */
    public $options;

    /**
     * @return ShippingDetailName
     */
    public function getName(): ShippingDetailName
    {
        return $this->name;
    }

    /**
     * @param ShippingDetailName $name
     */
    public function setName(ShippingDetailName $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return ShippingDetailAddress
     */
    public function getAddress(): ShippingDetailAddress
    {
        return $this->address;
    }

    /**
     * @param ShippingDetailAddress $address
     */
    public function setAddress(ShippingDetailAddress $address)
    {
        $this->address = $address;
    }

    /**
     * @return ShippingDetailOption[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param ShippingDetailOption[] $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;

        // Force one option to be selected
        $selectedOptions = array_filter($this->options, function ($option) {
            return $option->selected;
        });
        if (count($this->options) && !count($selectedOptions)) {
            $this->options[0]->setSelected(true);
        }
    }

    public function resetOptions()
    {
        $this->options = null;
    }
}
