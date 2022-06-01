<?php

namespace Aggrosoft\PayPal\Application\Core;

use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Response\Order\OrderResponseHandler;
use Aggrosoft\PayPal\Application\Core\Factory\Request\Order\CreateOrderRequestFactory;

class PayPalInitiator
{
    /**
     * Create PayPal order and redirect
     */
    public function initiate ($returnUrl = '', $noRedirect = false)
    {
        $returnToken = $this->generateReturnToken();
        $savedBasket = $this->savePayPalBasket($returnToken);

        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        $basket = $session->getBasket();
        $user = $basket->getBasketUser();
        $payment = $this->getPayment();

        $request = CreateOrderRequestFactory::create($user, $basket, $payment, $returnUrl . '&pptoken='.$returnToken);
        $client = $this->getPayPalClient();

        $response = $client->execute($request);

        $redirectUrl = OrderResponseHandler::handle($response, $savedBasket);

        if ($redirectUrl && !$noRedirect) {
            \OxidEsales\Eshop\Core\Registry::getUtils()->redirect($redirectUrl, false, 303);
        } else {
            \OxidEsales\Eshop\Core\Registry::getSession()->setVariable('pptoken', $response->id);
        }

        return [
            'orderId' => $response->id,
            'returnToken' => $returnToken
        ];
    }

    /**
     * Save current user basket to database
     * @return void
     */
    protected function savePayPalBasket ($returnToken)
    {
        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        $basket = $session->getBasket();
        $user = $basket->getBasketUser();

        if ($user) {
            $savedBasket = $user->getBasket('paypalbasket');
            $savedBasket->delete();
        } else {
            $savedBasket = oxNew(\OxidEsales\Eshop\Application\Model\UserBasket::class);
            $savedBasket->oxuserbaskets__oxtitle = new \OxidEsales\Eshop\Core\Field('paypalbasket');
            $savedBasket->setIsNewBasket();
        }

        $contents = $basket->getContents();

        if (!($deliveryAddressId = \OxidEsales\Eshop\Core\Registry::getRequest()->getRequestEscapedParameter('deladrid'))) {
            $deliveryAddressId = \OxidEsales\Eshop\Core\Registry::getSession()->getVariable('deladrid');
        }

        $savedBasket->oxuserbaskets__agpaypalreturntoken = new \OxidEsales\Eshop\Core\Field($returnToken);
        $savedBasket->oxuserbaskets__agpaypalpaymentid = new \OxidEsales\Eshop\Core\Field($basket->getPaymentId());
        $savedBasket->oxuserbaskets__agpaypalshippingid = new \OxidEsales\Eshop\Core\Field($basket->getShippingId());
        $savedBasket->oxuserbaskets__agpaypaldeladrid = new \OxidEsales\Eshop\Core\Field($deliveryAddressId);
        $savedBasket->oxuserbaskets__agpaypalremark = new \OxidEsales\Eshop\Core\Field(\OxidEsales\Eshop\Core\Registry::getSession()->getVariable('ordrem'), \OxidEsales\Eshop\Core\Field::T_RAW);
        $savedBasket->oxuserbaskets__agpaypalcardid = new \OxidEsales\Eshop\Core\Field($basket->getCardId());
        $savedBasket->oxuserbaskets__agpaypalcardtext = new \OxidEsales\Eshop\Core\Field($basket->getCardMessage());

        foreach ($contents as $basketItem) {
            if (!$basketItem->isBundle() && !$basketItem->isDiscountArticle()) {
                $savedBasket->addPayPalItemToBasket($basketItem->getProductId(), $basketItem->getAmount(), $basketItem->getSelList(), true, $basketItem->getPersParams(), $basketItem->getWrappingId());
            }
        }

        return $savedBasket;
    }

    protected function getPayPalPaymentMethod ()
    {
        $payment = $this->getPayment();
        return $payment ? $payment->oxpayments__agpaypalpaymentmethod->value : null;
    }

    protected function getPayPalClient ()
    {
        if (!$this->paypalClient) {
            $this->paypalClient = new PayPalRestClient();
        }
        return $this->paypalClient;
    }

    protected function getPayment ()
    {
        if(!$this->payment) {
            $session = \OxidEsales\Eshop\Core\Registry::getSession();
            if (!($paymentId = \OxidEsales\Eshop\Core\Registry::getRequest()->getRequestEscapedParameter('paymentid'))) {
                $paymentId = $session->getVariable('paymentid');
            }

            if($paymentId) {
                $this->payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
                $this->payment->load($paymentId);
            }
        }
        return $this->payment;
    }

    protected function generateReturnToken ()
    {
        return bin2hex(random_bytes(64));
    }
}