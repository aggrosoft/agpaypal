<?php

namespace Aggrosoft\PayPal\Application\Controller;

use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\ApplicationContext;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\UpdateOrderPurchaseUnitsRequest;
use Aggrosoft\PayPal\Application\Core\Factory\Request\Order\CreateOrderRequestFactory;
use Aggrosoft\PayPal\Application\Core\PayPalBasketHandler;
use Aggrosoft\PayPal\Application\Core\PayPalHelper;
use Aggrosoft\PayPal\Application\Core\PayPalInitiator;
use OxidEsales\Eshop\Core\Registry;

class ArticleDetailsController extends ArticleDetailsController_parent
{
    // Used for express checkout
    public function createpaypalorder()
    {
        $session = Registry::getSession();
        $paypalPaymentId = PayPalHelper::getPayPalPaymentId();
        $session->setVariable('paymentid', $paypalPaymentId);
        $session->getBasket()->setPayment($paypalPaymentId);
        $paypal = new PayPalInitiator(Registry::getConfig()->getCurrentShopUrl() . 'index.php?cl=order&fnc=ppreturn');
        $paypal->setShippingPreference($this->getUser() ? ApplicationContext::SHIPPING_PREFERENCE_NO_SHIPPING : ApplicationContext::SHIPPING_PREFERENCE_GET_FROM_FILE);
        $paypal->setRedirect(false);
        $paypal->setProducts(json_decode(Registry::getRequest()->getRequestParameter('products'), true));
        $response = $paypal->initiate();
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // Called when user changes shipping address in paypal frame
    public function updatepaypalpurchaseunits()
    {
        $userBasket = PayPalBasketHandler::getUserBasketForTokenPair(Registry::getRequest()->getRequestEscapedParameter('token'), Registry::getRequest()->getRequestEscapedParameter('pptoken'));
        $basket = PayPalBasketHandler::restoreBasketFromUserBasket($userBasket, $this->getUser());
        $user = $basket->getBasketUser();

        $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $purchaseUnits = CreateOrderRequestFactory::createPurchaseUnitRequest($user, $basket, $this->getUser() ? ApplicationContext::SHIPPING_PREFERENCE_NO_SHIPPING : ApplicationContext::SHIPPING_PREFERENCE_GET_FROM_FILE,  $country->getIdByCode(Registry::getRequest()->getRequestEscapedParameter('ppcountryid')));

        if (count($purchaseUnits->shipping->options)) {
            $client = new PayPalRestClient();
            $request = new UpdateOrderPurchaseUnitsRequest(Registry::getRequest()->getRequestEscapedParameter('token'), $purchaseUnits);
            $client->execute($request);
            $result = true;
        } else {
            $result = false;
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }
}
