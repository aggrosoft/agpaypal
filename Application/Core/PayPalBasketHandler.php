<?php

namespace Aggrosoft\PayPal\Application\Core;

use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class PayPalBasketHandler
{
    /**
     * Save current user basket to database
     * @return void
     */
    public static function savePayPalBasket($returnToken, $products = null)
    {
        $session = Registry::getSession();
        $basket = $session->getBasket();
        $user = $basket->getBasketUser();

        if ($user) {
            $savedBasket = $user->getBasket('paypalbasket');
            $savedBasket->delete();
        } else {
            $savedBasket = oxNew(\OxidEsales\Eshop\Application\Model\UserBasket::class);
            $savedBasket->oxuserbaskets__oxtitle = new Field('paypalbasket');
            $savedBasket->setIsNewBasket();
        }

        if (!($deliveryAddressId = Registry::getRequest()->getRequestEscapedParameter('deladrid'))) {
            $deliveryAddressId = Registry::getSession()->getVariable('deladrid');
        }

        $savedBasket->oxuserbaskets__agpaypalreturntoken = new Field($returnToken);
        $savedBasket->oxuserbaskets__agpaypalpaymentid = new Field($basket->getPaymentId());
        $savedBasket->oxuserbaskets__agpaypalshippingid = new Field($basket->getShippingId());
        $savedBasket->oxuserbaskets__agpaypaldeladrid = new Field($deliveryAddressId);
        $savedBasket->oxuserbaskets__agpaypalremark = new Field(Registry::getSession()->getVariable('ordrem'), Field::T_RAW);
        $savedBasket->oxuserbaskets__agpaypalcardid = new Field($basket->getCardId());
        $savedBasket->oxuserbaskets__agpaypalcardtext = new Field($basket->getCardMessage());
        $savedBasket->oxuserbaskets__agpaypalvouchers = new Field(json_encode(array_map(function ($voucher) {
            return $voucher->sVoucherNr;
        }, $basket->getVouchers())), Field::T_RAW);

        if ($products) {
            foreach ($products as $product) {
                $savedBasket->addPayPalItemToBasket($product['id'], $product['amount'], $product['sellists'], true, $product['persparam'], $product['wrapping']);
            }
        } else {
            $contents = $basket->getContents();

            foreach ($contents as $basketItem) {
                if (!$basketItem->isBundle() && !$basketItem->isDiscountArticle()) {
                    $savedBasket->addPayPalItemToBasket($basketItem->getProductId(), $basketItem->getAmount(), $basketItem->getSelList(), true, $basketItem->getPersParams(), $basketItem->getWrappingId());
                }
            }
        }

        foreach ($basket->getVouchers() as $voucherId => $voucher) {
            $basket->removeVoucher($voucherId);
        }

        return $savedBasket;
    }

    public static function updateUserBasketShipping($userBasket, $shippingId)
    {
        $userBasket->oxuserbaskets__agpaypalshippingid = new Field($shippingId);
        $userBasket->save();
    }

    public static function restoreBasketFromUserBasket($userBasket, $user)
    {
        // Registry::getSession()->getBasket()->setDisableArtStockInBasket(true);
        // Registry::getSession()->delBasket();

        $basket = oxNew(\OxidEsales\Eshop\Application\Model\Basket::class);
        Registry::getSession()->setBasket($basket);
        //$basket->setDisableArtStockInBasket(true);
        $basket->setBasketUser($user);
        $basket->setPayment(Registry::getRequest()->getRequestEscapedParameter('paymentid') ?: $userBasket->oxuserbaskets__agpaypalpaymentid->value);
        $basket->setShipping(Registry::getRequest()->getRequestEscapedParameter('shippingid') ?: $userBasket->oxuserbaskets__agpaypalshippingid->value);
        $basket->setCardId($userBasket->oxuserbaskets__agpaypalcardid->value);
        $basket->setCardMessage($userBasket->oxuserbaskets__agpaypalcardtext->value);
        if ($user) {
            $user->setSelectedAddressId($userBasket->oxuserbaskets__agpaypaldeladrid->value);
            Registry::get(\OxidEsales\Eshop\Application\Model\VatSelector::class)->getUserVat($user, true);
        }
        Registry::getSession()->setVariable('deladrid', $userBasket->oxuserbaskets__agpaypaldeladrid->value);
        Registry::getSession()->setVariable('ordrem', $userBasket->oxuserbaskets__agpaypalremark->value);
        Registry::getSession()->setVariable('sShipSet', $basket->getShippingId());

        $aSavedItems = $userBasket->getItems();
        foreach ($aSavedItems as $oItem) {
            $oSelList = $oItem->getSelList();

            $basketItem = $basket->addToBasket($oItem->oxuserbasketitems__oxartid->value, $oItem->oxuserbasketitems__oxamount->value, $oSelList, $oItem->getPersParams(), true);
            $basketItem->setWrapping($oItem->oxuserbasketitems__agpaypalwrapid->value);
        }

        $vouchers = $userBasket->oxuserbaskets__agpaypalvouchers->rawValue ? json_decode($userBasket->oxuserbaskets__agpaypalvouchers->rawValue) : [];

        $basket->calculateBasket();

        //$basket->setSkipVouchersChecking(true);
        foreach ($vouchers as $voucher) {
            $basket->addRestoredVoucherForPayPal($voucher);
        }
        //$basket->setSkipVouchersChecking(false);

        $basket->calculateBasket();

        //$basket->setDisableArtStockInBasket(false);

        return $basket;
    }

    public static function getUserBasketForTokenPair($token, $pptoken)
    {
        if (class_exists('\OxidEsales\EshopCommunity\Internal\Container\ContainerFactory')) {
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
        } else {
            $rs = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->select('SELECT oxid FROM oxuserbaskets WHERE oxuserbaskets.agpaypaltoken = :token AND oxuserbaskets.agpaypalreturntoken = :pptoken', ['token' => $token, 'pptoken' => $pptoken]);
            $basketId = current($rs->getFields());
        }

        if ($basketId) {
            $basket = oxNew(\OxidEsales\Eshop\Application\Model\UserBasket::class);
            $basket->load($basketId);
            return $basket;
        }
    }


    public static function getUserBasketForToken($token)
    {
        if (class_exists('\OxidEsales\EshopCommunity\Internal\Container\ContainerFactory')) {
            $container = ContainerFactory::getInstance()->getContainer();
            $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);
            $queryBuilder = $queryBuilderFactory->create();

            $data = $queryBuilder->select('oxid')
                ->from('oxuserbaskets')
                ->where('oxuserbaskets.agpaypaltoken = :token')
                ->setParameter('token', $token)
                ->execute();

            $basketId = $data->fetchColumn();
        } else {
            $rs = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->select('SELECT oxid FROM oxuserbaskets WHERE oxuserbaskets.agpaypaltoken = :token', ['token' => $token]);
            $basketId = current($rs->getFields());
        }

        if ($basketId) {
            $basket = oxNew(\OxidEsales\Eshop\Application\Model\UserBasket::class);
            $basket->load($basketId);
            return $basket;
        }
    }

    public static function destroyUserBasketForToken($token)
    {
        if (class_exists('\OxidEsales\EshopCommunity\Internal\Container\ContainerFactory')) {
            $container = ContainerFactory::getInstance()->getContainer();
            $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);
            $queryBuilder = $queryBuilderFactory->create();

            $data = $queryBuilder->select('oxid')
                ->from('oxuserbaskets')
                ->where('oxuserbaskets.agpaypaltoken = :token')
                ->setParameter('token', $token)
                ->execute();

            $basketId = $data->fetchColumn();
        } else {
            $rs = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->select('SELECT oxid FROM oxuserbaskets WHERE oxuserbaskets.agpaypaltoken = :token', ['token' => $token]);
            $basketId = current($rs->getFields());
        }


        if ($basketId) {
            $basket = oxNew(\OxidEsales\Eshop\Application\Model\UserBasket::class);
            $basket->load($basketId);
            $basket->delete();
        }
    }

    public static function destroyCurrentPayPalUserBasket()
    {
        $session = Registry::getSession();
        $basket = $session->getBasket();
        $user = $basket->getBasketUser();

        if ($user) {
            $savedBasket = $user->getBasket('paypalbasket');
            $savedBasket->delete();
        }
    }
}
