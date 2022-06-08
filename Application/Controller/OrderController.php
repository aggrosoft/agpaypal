<?php

namespace Aggrosoft\PayPal\Application\Controller;

use Aggrosoft\PayPal\Application\Core\Client\Exception\RestException;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\ApplicationContext;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;
use Aggrosoft\PayPal\Application\Core\PayPalBasketHandler;
use Aggrosoft\PayPal\Application\Core\PayPalInitiator;
use Aggrosoft\PayPal\Application\Core\PayPalUserHandler;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class OrderController extends OrderController_parent
{

    public function ppreturn ()
    {
        $token = Registry::getRequest()->getRequestEscapedParameter('token');
        $pptoken = Registry::getRequest()->getRequestEscapedParameter('pptoken');

        if(!$pptoken) {
            $pptoken = Registry::getSession()->getVariable('pptoken');
        }

        //Is there a basket for this token
        $userBasket = PayPalBasketHandler::getUserBasketForToken($token, $pptoken);

        if ($userBasket) {
            // auth user
            if (!$userBasket->oxuserbaskets__oxuserid->value) {
                $userId = PayPalUserHandler::getUserFromPayPalToken($token);
            }else{
                $userId = $userBasket->oxuserbaskets__oxuserid->value;
            }

            Registry::getSession()->setVariable('usr', $userId);
            $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
            $user->loadActiveUser();

            self::$_oActUser = $user;

            // load basket
            $basket = PayPalBasketHandler::restoreBasketFromUserBasket($userBasket, $user);
            $this->_oShipSet = $this->_oBasket = $this->_oPayment = null;
            Registry::getSession()->setBasket($basket);
            $userBasket->delete();

            // store paypal token for capturing on execute
            Registry::getSession()->setVariable('pptoken', $token);

            if(Registry::getRequest()->getRequestEscapedParameter('execute')) {
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
    }

    public function executepaypal ()
    {
        $paypal = new PayPalInitiator(Registry::getConfig()->getCurrentShopUrl() . 'index.php?cl=order&fnc=ppreturn&execute=1&sDeliveryAddressMD5='.$this->getDeliveryAddressMD5());
        $paypal->setUserAction(ApplicationContext::USER_ACTION_PAY_NOW);
        try {
            $paypal->initiate();
        }catch(RestException $ex){
            Registry::getUtilsView()->addErrorToDisplay($ex);
            return 'payment';
        }

    }

    public function getExecuteFnc()
    {
        $payment = $this->getPayment();
        if ($payment && $payment->oxpayments__agpaypalpaymentmethod->value){
            if (!Registry::getSession()->getVariable('pptoken') && $payment->oxpayments__agpaypalpaymentmethod->value !== PaymentSource::CARD && $payment->oxpayments__agpaypalpaymentmethod->value !== PaymentSource::PAY_UPON_INVOICE){
                return 'executepaypal';
            }
        }
        return parent::getExecuteFnc();
    }

}