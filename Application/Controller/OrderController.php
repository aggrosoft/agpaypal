<?php

namespace Aggrosoft\PayPal\Application\Controller;

use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\GetOrderRequest;
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
        $userBasket = $this->getUserBasketForToken($token, $pptoken);

        if ($userBasket) {
            // auth user
            if (!$userBasket->oxuserbaskets__oxuserid->value) {
                $userId = $this->getUserFromPayPalToken($token);
            }else{
                $userId = $userBasket->oxuserbaskets__oxuserid->value;
            }

            Registry::getSession()->setVariable('usr', $userId);
            $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
            $user->loadActiveUser();

            self::$_oActUser = $user;

            // load basket
            $basket = $this->restoreBasketFromUserBasket($userBasket, $user);
            Registry::getSession()->setBasket($basket);
            $userBasket->delete();

            // store paypal token for capturing on execute
            Registry::getSession()->setVariable('pptoken', $token);
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

    protected function restoreBasketFromUserBasket ($userBasket, $user)
    {
        $basket = oxNew(\OxidEsales\Eshop\Application\Model\Basket::class);
        $basket->setBasketUser($user);
        $basket->setPayment(Registry::getRequest()->getRequestEscapedParameter('paymentid') ?: $userBasket->oxuserbaskets__agpaypalpaymentid->value);
        $basket->setShipping(Registry::getRequest()->getRequestEscapedParameter('shippingid') ?: $userBasket->oxuserbaskets__agpaypalshippingid->value);
        $basket->setCardId($userBasket->oxuserbaskets__agpaypalcardid->value);
        $basket->setCardMessage($userBasket->oxuserbaskets__agpaypalcardtext->value);
        Registry::getSession()->setVariable('deladrid', $userBasket->oxuserbaskets__agpaypaldeladrid->value);
        Registry::getSession()->setVariable('ordrem', $userBasket->oxuserbaskets__agpaypalremark->value);
        Registry::getSession()->setVariable('sShipSet', $basket->getShippingId());

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

    protected function getUserFromPayPalToken($token)
    {
        $client = new PayPalRestClient();
        $request = new GetOrderRequest($token);
        $response = $client->execute($request);
        $payer = $response->payer;
        $shipping = $response->purchase_units[0]->shipping;

        $userId = $this->getUserIdByPayPalPayerId($payer->payer_id);
        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);

        if ($userId) {
            $user->load($userId);
        }

        $name = explode(" ", $shipping->name->full_name);
        $firstName = array_shift($name);
        $lastName = implode(" ", $name);

        $user->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field($payer->email_address);
        $user->oxuser__agpaypalpayerid = new \OxidEsales\Eshop\Core\Field($payer->payer_id);
        $user->oxuser__oxfname = new \OxidEsales\Eshop\Core\Field($firstName);
        $user->oxuser__oxlname = new \OxidEsales\Eshop\Core\Field($lastName);

        //@TODO: this really needs to be properly checked with test data
        $address = \VIISON\AddressSplitter\AddressSplitter::splitAddress($shipping->address->address_line_1);

        $user->oxuser__oxcity = new \OxidEsales\Eshop\Core\Field($shipping->address->admin_area_1);
        $user->oxuser__oxzip = new \OxidEsales\Eshop\Core\Field($shipping->address->postal_code);
        $user->oxuser__oxstreet = new \OxidEsales\Eshop\Core\Field($address['additionToAddress1'] . $address['streetName']);
        $user->oxuser__oxstreetnr = new \OxidEsales\Eshop\Core\Field($address['houseNumber']);
        $user->oxuser__oxaddinfo = new \OxidEsales\Eshop\Core\Field($address['additionToAddress2'] . $shipping->address->address_line_2 . $shipping->address->address_line_3);

        $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $countryId = $country->getIdByCode($shipping->address->country_code);
        $user->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field($countryId);

        if ($payer->phone){
            $user->oxuser__oxfon = new \OxidEsales\Eshop\Core\Field($payer->phone->phone_number);
        }

        $user->save();
        return $user->getId();
    }

    protected function getUserIdByPayPalPayerId($payerId)
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);
        $queryBuilder = $queryBuilderFactory->create();

        $data = $queryBuilder->select('oxid')
            ->from('oxuser')
            ->where('oxuser.agpaypalpayerid = :payerid')
            ->setParameter('payerid', $payerId)
            ->execute();

        return $data->fetchColumn();
    }
}