<?php

namespace Aggrosoft\PayPal\Application\Core;

use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\ApplicationContext;
use Aggrosoft\PayPal\Application\Core\Client\Response\Order\OrderResponseHandler;
use Aggrosoft\PayPal\Application\Core\Factory\Request\Order\CreateOrderRequestFactory;

class PayPalInitiator
{
    /**
     * Create PayPal order and redirect
     */
    public function initiate ($returnUrl = '', $noRedirect = false, $shippingPreference = ApplicationContext::SHIPPING_PREFERENCE_SET_PROVIDED_ADDRESS)
    {
        $returnToken = $this->generateReturnToken();
        $savedBasket = PayPalBasketHandler::savePayPalBasket($returnToken);

        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        $basket = $session->getBasket();
        $user = $basket->getBasketUser();
        $payment = $this->getPayment();

        $request = CreateOrderRequestFactory::create($user, $basket, $payment, $returnUrl . '&pptoken='.$returnToken, $shippingPreference);
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