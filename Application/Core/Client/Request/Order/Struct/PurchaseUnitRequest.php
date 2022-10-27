<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class PurchaseUnitRequest extends RequestObject
{
    /** @var string */
    public $reference_id;
    /** @var AmountWithBreakdown */
    public $amount;
    /** @var Payee */
    public $payee;
    /** @var object */
    public $payment_instruction;
    /** @var string */
    public $description;
    /** @var string */
    public $custom_id;
    /** @var string */
    public $invoice_id;
    /** @var string */
    public $soft_descriptor;
    /** @var array<Item> */
    public $items;
    /** @var ShippingDetail */
    public $shipping;

    /**
     * @return string
     */
    public function getReferenceId(): string
    {
        return $this->reference_id;
    }

    /**
     * @param string $reference_id
     */
    public function setReferenceId(string $reference_id)
    {
        $this->reference_id = $reference_id;
    }

    /**
     * @return AmountWithBreakdown
     */
    public function getAmount(): AmountWithBreakdown
    {
        return $this->amount;
    }

    /**
     * @param AmountWithBreakdown $amount
     */
    public function setAmount(AmountWithBreakdown $amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return Payee
     */
    public function getPayee(): Payee
    {
        return $this->payee;
    }

    /**
     * @param Payee $payee
     */
    public function setPayee(Payee $payee)
    {
        $this->payee = $payee;
    }

    /**
     * @return object
     */
    public function getPaymentInstruction()
    {
        return $this->payment_instruction;
    }

    /**
     * @param object $payment_instruction
     */
    public function setPaymentInstruction($payment_instruction)
    {
        $this->payment_instruction = $payment_instruction;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCustomId(): string
    {
        return $this->custom_id;
    }

    /**
     * @param string $custom_id
     */
    public function setCustomId(string $custom_id)
    {
        $this->custom_id = $custom_id;
    }

    /**
     * @return string
     */
    public function getInvoiceId(): string
    {
        return $this->invoice_id;
    }

    /**
     * @param string $invoice_id
     */
    public function setInvoiceId(string $invoice_id)
    {
        $this->invoice_id = $invoice_id;
    }

    /**
     * @return string
     */
    public function getSoftDescriptor(): string
    {
        return $this->soft_descriptor;
    }

    /**
     * @param string $soft_descriptor
     */
    public function setSoftDescriptor(string $soft_descriptor)
    {
        $this->soft_descriptor = $soft_descriptor;
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param Item[] $items
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return ShippingDetail
     */
    public function getShipping(): ShippingDetail
    {
        return $this->shipping;
    }

    /**
     * @param ShippingDetail $shipping
     */
    public function setShipping(ShippingDetail $shipping)
    {
        $this->shipping = $shipping;
    }

    public function unsetShipping() {
        $this->shipping = null;
    }
}
