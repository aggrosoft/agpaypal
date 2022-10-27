<?php

namespace Aggrosoft\PayPal\Application\Model;

use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\Money;
use Aggrosoft\PayPal\Application\Core\Client\Request\Payments\Captures\RefundCapturedPaymentRequest;

class OrderArticle extends OrderArticle_parent
{
    public function __call($method, $arguments)
    {
        if(method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $arguments);
        }elseif($this->getArticle() && method_exists($this->getArticle(), $method)) {
            return call_user_func_array([$this->getArticle(), $method], $arguments);
        }
    }

    public function cancelOrderArticle()
    {
        parent::cancelOrderArticle();
        $this->cancelPayPalOrderArticle();
    }

    public function cancelPayPalOrderArticle()
    {
        $order = $this->getOrder();

        if ($order->oxorder__oxstorno->value !== 1) {
            $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
            $payment->load($order->oxorder__oxpaymenttype->value);

            if ($payment->oxpayments__agpaypalpaymentmethod->value && $order->oxorder__agpaypalcaptureid->value) {
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

    public function getIconUrl($iIndex = 0)
    {
        return $this->getArticle()->getIconUrl($iIndex);
    }

    public function getLink()
    {
        return $this->getArticle()->getLink();
    }
}
