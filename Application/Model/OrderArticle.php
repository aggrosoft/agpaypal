<?php

namespace Aggrosoft\PayPal\Application\Model;

use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\Money;
use Aggrosoft\PayPal\Application\Core\Client\Request\Payments\Captures\RefundCapturedPaymentRequest;

class OrderArticle extends OrderArticle_parent
{
    public function cancelOrderArticle ()
    {
        parent::cancelOrderArticle();
        $this->cancelPayPalOrderArticle();
    }

    public function cancelPayPalOrderArticle ()
    {
        $order = $this->getOrder();

        if ($order->oxorder__oxstorno->value !== 1) {
            $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
            $payment->load($order->oxorder__oxpaymenttype->value);

            if ( $payment->oxpayments__agpaypalpaymentmethod->value && $order->oxorder__agpaypalcaptureid->value ) {
                $client = new PayPalRestClient();
                $request = new RefundCapturedPaymentRequest($order->oxorder__agpaypalcaptureid->value);
                $request->setAmount(new Money($order->oxorder__oxcurrency->value, $this->oxorderarticles__oxbrutprice->value));
                $response = $client->execute($request);

                $order->oxorder__agpaypalrefundid = new \OxidEsales\Eshop\Core\Field($response->id);
                $order->oxorder__agpaypaltransstatus = new \OxidEsales\Eshop\Core\Field('PARTIALLY_REFUNDED');
                $order->save();
            }
        }
    }
}