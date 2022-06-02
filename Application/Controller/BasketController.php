<?php

namespace Aggrosoft\PayPal\Application\Controller;

use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Request\Identity\GenerateTokenRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\ApplicationContext;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\UpdateOrderPurchaseUnitsRequest;
use Aggrosoft\PayPal\Application\Core\Factory\Request\Order\CreateOrderRequestFactory;
use Aggrosoft\PayPal\Application\Core\PayPalInitiator;
use OxidEsales\Eshop\Core\Registry;

class BasketController extends BasketController_parent
{

    // Used for express checkout
    public function createpaypalorder ()
    {
        $session = Registry::getSession();
        $session->setVariable('paymentid', Registry::getRequest()->getRequestEscapedParameter('paymentid'));
        $paypal = new PayPalInitiator();
        $response = $paypal->initiate(Registry::getConfig()->getCurrentShopUrl() . 'index.php?cl=order&fnc=execute', true, ApplicationContext::SHIPPING_PREFERENCE_GET_FROM_FILE);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // Called when user changes shipping address in paypal frame
    public function updatepaypalpurchaseunits ()
    {
        $session = Registry::getSession();
        $basket = $session->getBasket();
        $user = $basket->getBasketUser();

        $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $purchaseUnits = CreateOrderRequestFactory::createPurchaseUnitRequest($user, $basket, ApplicationContext::SHIPPING_PREFERENCE_GET_FROM_FILE, $country->getIdByCode(Registry::getRequest()->getRequestEscapedParameter('ppcountryid')));

        if (count($purchaseUnits->shipping->options)){
            $client = new PayPalRestClient();
            $request = new UpdateOrderPurchaseUnitsRequest($session->getVariable('pptoken'), $purchaseUnits);
            $client->execute($request);
            $result = true;
        }else{
            $result = false;
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }

}