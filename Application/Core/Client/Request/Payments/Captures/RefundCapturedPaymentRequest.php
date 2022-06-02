<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Payments\Captures;

use Aggrosoft\PayPal\Application\Core\Client\Request\IPayPalRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\JSONBodyTrait;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\ApplicationContext;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\Money;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\Payer;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PurchaseUnitRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class RefundCapturedPaymentRequest extends RequestObject implements IPayPalRequest
{

    use JSONBodyTrait;

    /**
     * @var string
     */
    protected $captureId;

    public function __construct ($captureId)
    {
        $this->captureId = $captureId;
    }

    /**
     * @var Money
     */
    public $amount;

    /**
     * @var string
     */
    public $invoice_id;

    /**
     * @var string
     */
    public $note_to_payer;

    public function getEndpoint()
    {
        return 'v2/payments/captures/'.$this->captureId.'/refund';
    }

    public function getHeaders()
    {
        return [];
    }

    public function getMethod()
    {
        return 'POST';
    }

    /**
     * @return Money
     */
    public function getAmount(): Money
    {
        return $this->amount;
    }

    /**
     * @param Money $amount
     */
    public function setAmount(Money $amount): void
    {
        $this->amount = $amount;
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
    public function setInvoiceId(string $invoice_id): void
    {
        $this->invoice_id = $invoice_id;
    }

    /**
     * @return string
     */
    public function getNoteToPayer(): string
    {
        return $this->note_to_payer;
    }

    /**
     * @param string $note_to_payer
     */
    public function setNoteToPayer(string $note_to_payer): void
    {
        $this->note_to_payer = $note_to_payer;
    }



}