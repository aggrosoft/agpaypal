<?php

namespace Aggrosoft\PayPal\Application\Core;

use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\GetOrderRequest;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class PayPalUserHandler
{
    public static function getUserFromPayPalToken($token)
    {
        $client = new PayPalRestClient();
        $request = new GetOrderRequest($token);
        $response = $client->execute($request);
        $payer = $response->payer;
        $paymentSource = $response->payment_source;
        $shipping = $response->purchase_units[0]->shipping;

        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        $userId = $user->getIdByUserName($paymentSource->paypal->email_address);

        if ($userId) {
            $user->load($userId);
        }

        $name = explode(" ", $shipping->name->full_name);
        $firstName = trim(array_shift($name));
        $lastName = trim(implode(" ", $name));

        $user->oxuser__oxusername = new \OxidEsales\Eshop\Core\Field($paymentSource->paypal->email_address);
        $user->oxuser__agpaypalpayerid = new \OxidEsales\Eshop\Core\Field($paymentSource->paypal->account_id);
        $user->oxuser__oxfname = new \OxidEsales\Eshop\Core\Field($firstName);
        $user->oxuser__oxlname = new \OxidEsales\Eshop\Core\Field($lastName);

        //@TODO: this really needs to be properly checked with test data

        try {
            $address = \VIISON\AddressSplitter\AddressSplitter::splitAddress($shipping->address->address_line_1 . ' ' . $shipping->address->address_line_2);
            $user->oxuser__oxcity = new \OxidEsales\Eshop\Core\Field($shipping->address->admin_area_2);
            $user->oxuser__oxzip = new \OxidEsales\Eshop\Core\Field($shipping->address->postal_code);
            $user->oxuser__oxstreet = new \OxidEsales\Eshop\Core\Field($address['additionToAddress1'] . $address['streetName']);
            $user->oxuser__oxstreetnr = new \OxidEsales\Eshop\Core\Field($address['houseNumber']);
            $user->oxuser__oxaddinfo = new \OxidEsales\Eshop\Core\Field($address['additionToAddress2'] . $shipping->address->address_line_3);
        } catch (\Exception $e) {
            $user->oxuser__oxcity = new \OxidEsales\Eshop\Core\Field($shipping->address->admin_area_2);
            $user->oxuser__oxzip = new \OxidEsales\Eshop\Core\Field($shipping->address->postal_code);
            $user->oxuser__oxstreet = new \OxidEsales\Eshop\Core\Field($shipping->address->address_line_1);
            $user->oxuser__oxstreetnr = new \OxidEsales\Eshop\Core\Field($shipping->address->address_line_2);
            $user->oxuser__oxaddinfo = new \OxidEsales\Eshop\Core\Field($shipping->address->address_line_3);
        }

        $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $countryId = $country->getIdByCode($shipping->address->country_code);
        $user->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field($countryId);

        $adminArea1 = $shipping->address->admin_area_1 === 'N/A' ? '' : $shipping->address->admin_area_1;

        if ($adminArea1) {
            $state = oxNew(\OxidEsales\Eshop\Application\Model\State::class);
            $stateId = $state->getIdByCode($adminArea1, $countryId);
            $user->oxuser__oxstateid = new \OxidEsales\Eshop\Core\Field($stateId);
        }

        if ($payer && $payer->phone) {
            $user->oxuser__oxfon = new \OxidEsales\Eshop\Core\Field($payer->phone->phone_number);
        }

        // random password if none yet, will allow to login later using forgot password
        //if (!$user->oxuser__oxpassword->value) {
        //    $user->setPassword(bin2hex(random_bytes(32)));
        //}

        $user->save();
        return $user->getId();
    }

    public static function getUserAddressFromPayPalToken($token, $userId)
    {
        $client = new PayPalRestClient();
        $request = new GetOrderRequest($token);
        $response = $client->execute($request);
        $payer = $response->payer;
        $shipping = $response->purchase_units[0]->shipping;

        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);

        if ($user->load($userId)) {
            $userAddress = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
            $userAddress->oxaddress__oxuserid = new \OxidEsales\Eshop\Core\Field($userId);

            $name = explode(" ", $shipping->name->full_name);
            $firstName = trim(array_shift($name));
            $lastName = trim(implode(" ", $name));

            $userAddress->oxaddress__oxfname = new \OxidEsales\Eshop\Core\Field($firstName);
            $userAddress->oxaddress__oxlname = new \OxidEsales\Eshop\Core\Field($lastName);

            if ($payer && $payer->phone) {
                $userAddress->oxaddress__oxfon = new \OxidEsales\Eshop\Core\Field($payer->phone->phone_number);
            }

            try {
                $address = \VIISON\AddressSplitter\AddressSplitter::splitAddress($shipping->address->address_line_1 . ' ' . $shipping->address->address_line_2);
                $userAddress->oxaddress__oxcity = new \OxidEsales\Eshop\Core\Field($shipping->address->admin_area_2);
                $userAddress->oxaddress__oxzip = new \OxidEsales\Eshop\Core\Field($shipping->address->postal_code);
                $userAddress->oxaddress__oxstreet = new \OxidEsales\Eshop\Core\Field($address['additionToAddress1'] . $address['streetName']);
                $userAddress->oxaddress__oxstreetnr = new \OxidEsales\Eshop\Core\Field($address['houseNumber']);
                $userAddress->oxaddress__oxaddinfo = new \OxidEsales\Eshop\Core\Field($address['additionToAddress2'] . $shipping->address->address_line_3);
            } catch (\Exception $e) {
                $userAddress->oxaddress__oxcity = new \OxidEsales\Eshop\Core\Field($shipping->address->admin_area_2);
                $userAddress->oxaddress__oxzip = new \OxidEsales\Eshop\Core\Field($shipping->address->postal_code);
                $userAddress->oxaddress__oxstreet = new \OxidEsales\Eshop\Core\Field($shipping->address->address_line_1);
                $userAddress->oxaddress__oxstreetnr = new \OxidEsales\Eshop\Core\Field($shipping->address->address_line_2);
                $userAddress->oxaddress__oxaddinfo = new \OxidEsales\Eshop\Core\Field($shipping->address->address_line_3);
            }

            $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
            $countryId = $country->getIdByCode($shipping->address->country_code);
            $userAddress->oxaddress__oxcountryid = new \OxidEsales\Eshop\Core\Field($countryId);

            $adminArea1 = $shipping->address->admin_area_1 === 'N/A' ? '' : $shipping->address->admin_area_1;

            if ($adminArea1) {
                $state = oxNew(\OxidEsales\Eshop\Application\Model\State::class);
                $stateId = $state->getIdByCode($adminArea1, $countryId);
                $userAddress->oxaddress__oxstateid = new \OxidEsales\Eshop\Core\Field($stateId);
            }

            $existingAddressId = $userAddress->getExistingAddressId();

            if ($existingAddressId) {
                return $existingAddressId;
            } else {
                $userAddress->oxaddress__agpaypalhash = new \OxidEsales\Eshop\Core\Field($userAddress->getPayPalAddressHash());
                $userAddress->save();
                return $userAddress->getId();
            }
        } else {
            throw new \OxidEsales\Eshop\Core\Exception\UserException('PAYPAL_ERROR_USER_NOT_FOUND');
        }
    }

    /**
     * @param $payerId
     * @return false|mixed
     * @deprecated must load by email address, oxid does not allow to have multiple users with same email
     */
    public static function getUserIdByPayPalPayerId($payerId)
    {
        if (class_exists('\OxidEsales\EshopCommunity\Internal\Container\ContainerFactory')) {
            $container = ContainerFactory::getInstance()->getContainer();
            $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);
            $queryBuilder = $queryBuilderFactory->create();

            $data = $queryBuilder->select('oxid')
                ->from('oxuser')
                ->where('oxuser.agpaypalpayerid = :payerid')
                ->andWhere('oxuser.oxpassword = "" OR oxuser.oxpassword IS NULL')
                ->setParameter('payerid', $payerId)
                ->execute();

            return $data->fetchColumn();
        } else {
            $rs = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->select('SELECT oxid FROM oxuser WHERE (oxuser.oxpassword = "" OR oxuser.oxpassword IS NULL) AND oxuser.agpaypalpayerid = :payerid', ['payerid' => $payerId]);
            return current($rs->getFields());
        }
    }
}
