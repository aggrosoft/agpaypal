<?php

namespace Aggrosoft\PayPal\Application\Controller;

use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;
use Aggrosoft\PayPal\Application\Core\PayPalInitiator;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class OrderController extends OrderController_parent
{
    public function ppreturn ()
    {
        $token = \OxidEsales\Eshop\Core\Registry::getRequest()->getRequestEscapedParameter('token');
        $pptoken = \OxidEsales\Eshop\Core\Registry::getRequest()->getRequestEscapedParameter('pptoken');

        //Is there a basket for this token
        $userBasket = $this->getUserBasketForToken($token, $pptoken);

        if ($userBasket) {
            // auth user
            \OxidEsales\Eshop\Core\Registry::getSession()->setVariable('usr', $userBasket->oxuserbaskets__oxuserid->value);
            $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
            $user->loadActiveUser();

            // load basket
            $basket = $this->restoreBasketFromUserBasket($userBasket);
            \OxidEsales\Eshop\Core\Registry::getSession()->setBasket($basket);
            $userBasket->delete();

            // store paypal token for capturing on execute
            \OxidEsales\Eshop\Core\Registry::getSession()->setVariable('pptoken', $token);
        }
    }

    protected function getUserBasketForToken ($token, $pptoken)
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);
        $queryBuilder = $queryBuilderFactory->create();

        $data = $queryBuilder->select('oxid')
            ->from('oxuserbaskets')
            ->where('oxuserbaskets.agpaypaltoken = :token')
            ->andWhere('oxuserbaskets.agpaypalreturntoken = :pptoken')
            ->setParameter('token', $token)
            ->setParameter('pptoken', $pptoken)
            ->execute();

        $basketId = $data->fetchColumn();

        if ($basketId) {
            $basket = oxNew(\OxidEsales\Eshop\Application\Model\UserBasket::class);
            $basket->load($basketId);
            return $basket;
        }
    }

    protected function restoreBasketFromUserBasket ($userBasket)
    {
        $basket = oxNew(\OxidEsales\Eshop\Application\Model\Basket::class);
        $basket->setPayment($userBasket->oxuserbaskets__agpaypalpaymentid->value);
        $basket->setShipping($userBasket->oxuserbaskets__agpaypalshippingid->value);
        $basket->setCardId($userBasket->oxuserbaskets__agpaypalcardid->value);
        $basket->setCardMessage($userBasket->oxuserbaskets__agpaypalcardtext->value);
        \OxidEsales\Eshop\Core\Registry::getSession()->setVariable('deladrid', $userBasket->oxuserbaskets__agpaypaldeladrid->value);
        \OxidEsales\Eshop\Core\Registry::getSession()->setVariable('ordrem', $userBasket->oxuserbaskets__agpaypalremark->value);

        $aSavedItems = $userBasket->getItems();
        foreach ($aSavedItems as $oItem) {
            try {
                $oSelList = $oItem->getSelList();

                $basketItem = $basket->addToBasket($oItem->oxuserbasketitems__oxartid->value, $oItem->oxuserbasketitems__oxamount->value, $oSelList, $oItem->getPersParams(), true);
                $basketItem->setWrapping($oItem->oxuserbasketitems__agpaypalwrapid->value);
            } catch (\OxidEsales\Eshop\Core\Exception\ArticleException $oEx) {
                // caught and ignored
            }
        }

        $basket->calculateBasket();

        return $basket;
    }
}