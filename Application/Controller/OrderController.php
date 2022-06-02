<?php

namespace Aggrosoft\PayPal\Application\Controller;

use Aggrosoft\PayPal\Application\Core\PayPalBasketHandler;
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
        }
    }

}