<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order;

use Aggrosoft\PayPal\Application\Core\Client\Request\IPayPalRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\JSONBodyTrait;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\ApplicationContext;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\Payer;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PurchaseUnitRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class CreateOrderRequest extends RequestObject implements IPayPalRequest
{
    use JSONBodyTrait;

    public const INTENT_CAPTURE = 'CAPTURE';
    public const INTENT_AUTHORIZE = 'AUTHORIZE';

    public const PROCESSING_INSTRUCTION_ORDER_COMPLETE_ON_PAYMENT_APPROVAL = 'ORDER_COMPLETE_ON_PAYMENT_APPROVAL';
    public const PROCESSING_INSTRUCTION_NO_INSTRUCTION = 'NO_INSTRUCTION';

    /**
     * @var string
     */
    public $metadataId;

    /**
     * @var string
     */
    public $intent;

    /**
     * @var string
     */
    public $processing_instruction;

    /**
     * @var Payer
     */
    public $payer;

    /**
     * @var array<PurchaseUnitRequest>
     */
    public $purchase_units = [];

    /**
     * @var ApplicationContext
     */
    public $application_context;

    /**
     * @var PaymentSource
     */
    public $payment_source;

    public function getEndpoint()
    {
        return 'v2/checkout/orders';
    }

    public function getHeaders()
    {
        return $this->metadataId ? ['PAYPAL-CLIENT-METADATA-ID' => $this->metadataId] : [];
    }

    public function getMethod()
    {
        return 'POST';
    }

    /**
     * @return string
     */
    public function getMetadataId(): string
    {
        return $this->metadataId;
    }

    /**
     * @param string $metadataId
     */
    public function setMetadataId(string $metadataId)
    {
        $this->metadataId = $metadataId;
    }

    /**
     * @return string
     */
    public function getIntent(): string
    {
        return $this->intent;
    }

    /**
     * @param string $intent
     */
    public function setIntent(string $intent)
    {
        $this->intent = $intent;
    }

    /**
     * @return string
     */
    public function getProcessingInstruction(): string
    {
        return $this->processing_instruction;
    }

    /**
     * @param string $processing_instruction
     */
    public function setProcessingInstruction(string $processing_instruction)
    {
        $this->processing_instruction = $processing_instruction;
    }

    /**
     * @return Payer
     */
    public function getPayer(): Payer
    {
        return $this->payer;
    }

    /**
     * @param Payer $payer
     */
    public function setPayer(Payer $payer)
    {
        $this->payer = $payer;
    }

    /**
     * @return PurchaseUnitRequest[]
     */
    public function getPurchaseUnits(): array
    {
        return $this->purchase_units;
    }

    /**
     * @param PurchaseUnitRequest $purchase_unit
     */
    public function addPurchaseUnit(PurchaseUnitRequest $purchase_unit)
    {
        $this->purchase_units[] = $purchase_unit;
    }

    /**
     * @param PurchaseUnitRequest[] $purchase_units
     */
    public function setPurchaseUnits(array $purchase_units)
    {
        $this->purchase_units = $purchase_units;
    }

    /**
     * @return ApplicationContext
     */
    public function getApplicationContext(): ApplicationContext
    {
        return $this->application_context;
    }

    /**
     * @param ApplicationContext $application_context
     */
    public function setApplicationContext(ApplicationContext $application_context)
    {
        $this->application_context = $application_context;
    }

    /**
     * @return PaymentSource
     */
    public function getPaymentSource(): PaymentSource
    {
        return $this->payment_source;
    }

    /**
     * @param PaymentSource $payment_source
     */
    public function setPaymentSource(PaymentSource $payment_source = null)
    {
        $this->payment_source = $payment_source;
    }
}
