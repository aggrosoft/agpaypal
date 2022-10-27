<?php

namespace Aggrosoft\PayPal\Application\Controller;

use Aggrosoft\PayPal\Application\Core\Client\Exception\RestException;
use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\GetOrderRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\ApplicationContext;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\UpdateOrderPurchaseUnitsRequest;
use Aggrosoft\PayPal\Application\Core\Factory\Request\Order\CreateOrderRequestFactory;
use Aggrosoft\PayPal\Application\Core\PayPalBasketHandler;
use Aggrosoft\PayPal\Application\Core\PayPalHelper;
use Aggrosoft\PayPal\Application\Core\PayPalInitiator;
use Aggrosoft\PayPal\Application\Core\PayPalUserHandler;
use OxidEsales\Eshop\Core\Registry;

class OrderController extends OrderController_parent
{
    // Used for paypal checkout
    public function createpaypalorder()
    {
        //$session = Registry::getSession();
        //$session->setVariable('paymentid', PayPalHelper::getPayPalPaymentId());
        $paypal = new PayPalInitiator(Registry::getConfig()->getCurrentShopUrl() . 'index.php?cl=order&fnc=ppreturn&execute=1');
        //$paypal->setShippingPreference(ApplicationContext::SHIPPING_PREFERENCE_GET_FROM_FILE);
        $paypal->setUserAction(ApplicationContext::USER_ACTION_PAY_NOW);
        $paypal->setRedirect(false);
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
        $purchaseUnits = CreateOrderRequestFactory::createPurchaseUnitRequest($user, $basket, ApplicationContext::SHIPPING_PREFERENCE_GET_FROM_FILE, $country->getIdByCode(Registry::getRequest()->getRequestEscapedParameter('ppcountryid')));

        if (count($purchaseUnits->shipping->options)) {
            if (count($purchaseUnits->shipping->options) === 1) {
                $purchaseUnits->unsetShipping();
            }
            PayPalBasketHandler::updateUserBasketShipping($userBasket, Registry::getRequest()->getRequestEscapedParameter('shippingid'));
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

    // Called when user changes shipping address in paypal frame
    public function getpaypalorder()
    {
        $client = new PayPalRestClient();
        $response = $client->execute(new GetOrderRequest(Registry::getRequest()->getRequestEscapedParameter('orderid')));

        header('$response-Type: application/json');
        echo json_encode($response);
        exit();
    }

    public function ppreturn()
    {
        $token = Registry::getRequest()->getRequestEscapedParameter('token');
        $pptoken = Registry::getRequest()->getRequestEscapedParameter('pptoken');
        $isExpressCheckout = Registry::getSession()->getVariable('ppexpresscomplete');
        Registry::getSession()->setVariable('ppexpresscomplete', 0);

        if (!$pptoken) {
            $pptoken = Registry::getSession()->getVariable('pptoken');
        }

        //Is there a basket for this token
        $userBasket = PayPalBasketHandler::getUserBasketForTokenPair($token, $pptoken);

        if ($userBasket) {
            // auth user
            if (!$userBasket->oxuserbaskets__oxuserid->value) {
                $userId = PayPalUserHandler::getUserFromPayPalToken($token);
                $shippingId = null;
            } else {
                $userId = $userBasket->oxuserbaskets__oxuserid->value;
                if ($isExpressCheckout) {
                    $shippingId = PayPalUserHandler::getUserAddressFromPayPalToken($token, $userBasket->oxuserbaskets__oxuserid->value);
                }
            }

            Registry::getSession()->setVariable('usr', $userId);
            $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
            $user->loadActiveUser();

            self::$_oActUser = $user;

            // load basket
            try {
                $basket = PayPalBasketHandler::restoreBasketFromUserBasket($userBasket, $user);
            } catch (\OxidEsales\Eshop\Core\Exception\ArticleException $e) {
                Registry::getUtilsView()->addErrorToDisplay('PAYPAL_ERROR_NOT_ALL_ARTICLES_BUYABLE');
                Registry::getUtils()->redirect(Registry::getConfig()->getCurrentShopUrl() . 'index.php?cl=basket');
            } catch (\OxidEsales\Eshop\Core\Exception\VoucherException $oEx) {
                // problems adding voucher
                \OxidEsales\Eshop\Core\Registry::getUtilsView()->addErrorToDisplay($oEx, false, true);
                Registry::getUtils()->redirect(Registry::getConfig()->getCurrentShopUrl() . 'index.php?cl=basket');
            } catch (\Exception $e) {
                Registry::getUtilsView()->addErrorToDisplay($e);
                Registry::getUtils()->redirect(Registry::getConfig()->getCurrentShopUrl() . 'index.php?cl=start');
            }

            $this->_oShipSet = $this->_oBasket = $this->_oPayment = null;
            if ($shippingId) {
                Registry::getSession()->setVariable('deladrid', $shippingId);
            }
            Registry::getSession()->setBasket($basket);
            $userBasket->delete();

            // store paypal token for capturing on execute
            Registry::getSession()->setVariable('pptoken', $token);
            Registry::getSession()->setVariable('ppexpresscomplete', 1);

            if (Registry::getRequest()->getRequestEscapedParameter('execute')) {
                try {
                    $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);

                    //finalizing ordering process (validating, storing order into DB, executing payment, setting status ...)
                    $iSuccess = $oOrder->finalizeOrder($basket, $user);

                    // performing special actions after user finishes order (assignment to special user groups)
                    $user->onOrderExecute($basket, $iSuccess);

                    // proceeding to next view
                    return $this->_getNextStep($iSuccess);
                } catch (\OxidEsales\Eshop\Core\Exception\OutOfStockException $oEx) {
                    $oEx->setDestination('basket');
                    Registry::getUtilsView()->addErrorToDisplay($oEx, false, true, 'basket');
                } catch (\OxidEsales\Eshop\Core\Exception\NoArticleException $oEx) {
                    Registry::getUtilsView()->addErrorToDisplay($oEx);
                } catch (\OxidEsales\Eshop\Core\Exception\ArticleInputException $oEx) {
                    Registry::getUtilsView()->addErrorToDisplay($oEx);
                }
            }
        }
        return 'order';
    }

    public function executepaypal()
    {
        $paypal = new PayPalInitiator(Registry::getConfig()->getCurrentShopUrl() . 'index.php?cl=order&fnc=ppreturn&execute=1&sDeliveryAddressMD5='.$this->getDeliveryAddressMD5());
        $paypal->setUserAction(ApplicationContext::USER_ACTION_PAY_NOW);
        try {
            $paypal->initiate();
        } catch (RestException $ex) {
            Registry::getUtilsView()->addErrorToDisplay($ex);
            return 'payment';
        }
    }

    public function getExecuteFnc()
    {
        $payment = $this->getPayment();
        if ($payment && $payment->oxpayments__agpaypalpaymentmethod->value) {
            if (!Registry::getSession()->getVariable('pptoken') && $payment->oxpayments__agpaypalpaymentmethod->value !== PaymentSource::CARD && $payment->oxpayments__agpaypalpaymentmethod->value !== PaymentSource::PAY_UPON_INVOICE) {
                return 'executepaypal';
            }
        }
        return parent::getExecuteFnc();
    }

    public function getPayPalFunding()
    {
        $payment = $this->getPayment();
        if ($payment && $payment->oxpayments__agpaypalpaymentmethod->value) {
            return str_replace('_', '', strtolower($payment->oxpayments__agpaypalpaymentmethod->value));
        }
        return false;
    }

    public function isPayPalExpressCheckout()
    {
        return Registry::getSession()->getVariable('ppexpresscomplete') === 1;
    }

    public function mustRenderPayPalButton()
    {
        $payment = $this->getPayment();
        if (
            !\OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('blPayPalRedirectOnCheckout', null, 'module:agpaypal') &&
            !$this->isPayPalExpressCheckout() &&
            $payment &&
            $payment->oxpayments__agpaypalpaymentmethod->value &&
            $payment->oxpayments__agpaypalpaymentmethod->value !== PaymentSource::CARD &&
            $payment->oxpayments__agpaypalpaymentmethod->value !== PaymentSource::PAY_UPON_INVOICE

        ) {
            return true;
        }
        return false;
    }
}
