<?php

namespace Aggrosoft\PayPal\Application\Core\Factory\Request\Order;

use Aggrosoft\PayPal\Application\Core\Client\Request\Order\CreateOrderRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\AddressPortable;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\AmountBreakdown;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\AmountWithBreakdown;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\ApplicationContext;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\Item;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\Money;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\Name;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\Payee;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\Payer;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentMethod;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSourceData;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PurchaseUnitRequest;

use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\ShippingDetail;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\ShippingDetailAddress;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\ShippingDetailName;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\ShippingDetailOption;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\StoredPaymentSource;
use Aggrosoft\PayPal\Application\Core\FraudNet;
use OxidEsales\Eshop\Core\Registry;

class CreateOrderRequestFactory
{
    private static $shippingOptions;

    /**
     * @param $user
     * @param $basket
     * @param $payment
     * @param $returnUrl
     * @param $orderNumber
     * @return CreateOrderRequest
     */
    public static function create($user, $basket, $payment, $returnUrl, $orderNumber = null, $shippingPreference = ApplicationContext::SHIPPING_PREFERENCE_SET_PROVIDED_ADDRESS, $userAction = ApplicationContext::USER_ACTION_CONTINUE)
    {
        //Cache this, used multiple times
        self::$shippingOptions = null;

        $request = new CreateOrderRequest();
        $request->setIntent(CreateOrderRequest::INTENT_CAPTURE);
        $request->setProcessingInstruction(self::isPayUponInvoice($payment) ? CreateOrderRequest::PROCESSING_INSTRUCTION_ORDER_COMPLETE_ON_PAYMENT_APPROVAL : CreateOrderRequest::PROCESSING_INSTRUCTION_NO_INSTRUCTION);
        if ($user) {
            $request->setPayer(self::createPayer($user));
        }
        $request->addPurchaseUnit(self::createPurchaseUnitRequest($user, $basket, $shippingPreference, null, $orderNumber, self::isPayUponInvoice($payment)));
        $request->setApplicationContext(self::createApplicationContext($returnUrl, $shippingPreference, $userAction, $payment->oxpayments__agpaypallandingpage->value));
        $request->setPaymentSource(self::createPaymentSource($user, $payment));
        if (self::isPayUponInvoice($payment)) {
            $request->setMetadataId(FraudNet::getSessionIdentifier());
        }

        return $request;
    }

    public static function createPurchaseUnitRequest($user, $basket, $shippingPreference, $countryId = null, $orderNumber = null, $separateTax = false)
    {
        $config = Registry::getConfig();
        $currencyName = $basket->getBasketCurrency()->name;

        $shippingOption = self::getSelectedShippingOption($user, $basket, $countryId);

        $basket->setShipping($shippingOption->id);
        $basket->calculateBasket(true);

        $unit = new PurchaseUnitRequest();
        $unit->setPayee(new Payee($config->getShopConfVar("sPayPalEmailAddress", null, "module:agpaypal")));
        $unit->setShipping(self::createShipping($user, $basket, $shippingPreference, $countryId));

        if ($orderNumber) {
            $unit->setInvoiceId($orderNumber);
        }

        $items = [];
        $itemTotal = 0;
        $taxTotal = 0;
        $hasDecimals = false;

        foreach ($basket->getContents() as $basketItem) {
            $article = $basketItem->getArticle();
            $item = new Item();
            $item->category = $article->oxarticles__oxnonmaterial->value ? 'DIGITAL_GOODS' : 'PHYSICAL_GOODS';
            $item->name = $basketItem->getTitle();
            if ($basketItem->getAmount() != round($basketItem->getAmount())) {
                $item->quantity = 1;
                $item->unit_amount = new Money($currencyName, $separateTax ? $basketItem->getPrice()->getNettoPrice() : $basketItem->getPrice()->getBruttoPrice());
            }else{
                $item->quantity = $basketItem->getAmount();
                $item->unit_amount = new Money($currencyName, $separateTax ? $basketItem->getUnitPrice()->getNettoPrice() : $basketItem->getUnitPrice()->getBruttoPrice());
            }

            if ($separateTax) {
                $item->tax = new Money($currencyName, $basketItem->getUnitPrice()->getVatValue());
                $item->tax_rate = $basketItem->getUnitPrice()->getVat();
            }

            $items[] = $item;
            $itemTotal += round($separateTax ? $basketItem->getUnitPrice()->getNettoPrice() : $basketItem->getUnitPrice()->getBruttoPrice(),2) * $item->quantity;
            $taxTotal += round($basketItem->getUnitPrice()->getVatValue(), 2) * $item->quantity;
            //if ($item->quantity != round($item->quantity)) {
            //    $hasDecimals = true;
            //}
        }

        $unit->setItems($items);

        $deliveryCosts = $basket->getDeliveryCost();

        $amountBreakDown = new AmountBreakdown();
        $amountBreakDown->item_total = new Money($currencyName, $itemTotal);
        if ($separateTax) {
            $amountBreakDown->tax_total = new Money($currencyName, $taxTotal);
        }

        $amountBreakDown->shipping = new Money($currencyName, $deliveryCosts->getBruttoPrice());
        $amountBreakDown->discount = new Money($currencyName, $basket->getTotalDiscountSum());

        //If any module adds some sort of extra costs (pawn, d3oqm etc.) we will just put those in handling fees
        $handling = $basket->getPrice()->getBruttoPrice() - $itemTotal - $deliveryCosts->getBruttoPrice() - $taxTotal + $basket->getTotalDiscountSum();

        if ($handling > 0) {
            $amountBreakDown->handling = new Money($currencyName, $handling);
        }
        //if ($countryId !== null || $shippingPreference !== ApplicationContext::SHIPPING_PREFERENCE_GET_FROM_FILE) {
        //    $unit->setAmount(new AmountWithBreakdown($currencyName, $basket->getPrice()->getBruttoPrice() - $deliveryCosts->getBruttoPrice()));
        //}else{
            $unit->setAmount(new AmountWithBreakdown($currencyName, $basket->getPrice()->getBruttoPrice(), $amountBreakDown));
        //}


        return $unit;
    }

    public static function createPayer($user)
    {
        $address = new AddressPortable();
        $deliveryAddress = self::getDelAddressInfo(); //I would rather not get this from a global, but this is oxid style

        if ($deliveryAddress) {
            $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
            $country->load($deliveryAddress->oxaddress__oxcountryid->value);

            $address->address_line_1 = $deliveryAddress->oxaddress__oxstreet->value . ' ' . $deliveryAddress->oxaddress__oxstreetnr->value;
            $address->postal_code = $deliveryAddress->oxaddress__oxzip->value;
            $address->country_code = $country->oxcountry__oxisoalpha2->value;
            $address->admin_area_2 = $deliveryAddress->oxaddress__oxcity->value;
        } else {
            $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
            $country->load($user->oxuser__oxcountryid->value);

            $address->address_line_1 = $user->oxuser__oxstreet->value . ' ' . $user->oxuser__oxstreetnr->value;
            $address->postal_code = $user->oxuser__oxzip->value;
            $address->country_code = $country->oxcountry__oxisoalpha2->value;
            $address->admin_area_2 = $user->oxuser__oxcity->value;
        }

        $payer = new Payer();
        $payer->setAddress($address);
        $payer->setEmailAddress($user->oxuser__oxusername->value);
        $payer->setName(new Name($user->oxuser__oxfname->value, $user->oxuser__oxlname->value));
        // $payer->setPhone($user->oxuser__oxfon->value); @TODO: this should be an object, also there are some requirements to allow sending this

        return $payer;
    }

    private static function getDelAddressInfo()
    {
        $oDelAdress = null;
        if (!($soxAddressId = Registry::getRequest()->getRequestEscapedParameter('deladrid'))) {
            $soxAddressId = Registry::getSession()->getVariable('deladrid');
        }
        if ($soxAddressId) {
            $oDelAdress = oxNew(\OxidEsales\Eshop\Application\Model\Address::class);
            if ($oDelAdress->load($soxAddressId)) {
                return $oDelAdress;
            } else {
                return null;
            }
        }
    }

    public static function createApplicationContext($returnUrl, $shippingPreference, $userAction, $landingPage)
    {
        $config = Registry::getConfig();
        $shop = $config->getActiveShop();

        $context = new ApplicationContext();
        $context->setBrandName($shop->oxshops__oxname->rawValue);
        $context->setLandingPage($landingPage ?: ApplicationContext::LANDING_PAGE_NO_PREFERENCE);
        $context->setShippingPreference($shippingPreference);
        $context->setUserAction($userAction);
        $context->setPaymentMethod(new PaymentMethod(PaymentMethod::PAYEE_PREFERRED_UNRESTRICTED));
        $context->setReturnUrl($returnUrl);
        $context->setCancelUrl(Registry::getConfig()->getCurrentShopUrl() . 'index.php?cl=payment');
        // $context->setStoredPaymentSource(new StoredPaymentSource());

        return $context;
    }

    public static function createPaymentSource($user, $payment)
    {
        $method = $payment->oxpayments__agpaypalpaymentmethod->value;

        if (self::isPayUponInvoice($payment)) {
            $source = new PaymentSource();
            $invoiceData = new \stdClass();
            $invoiceData->name = [
                'given_name' => $user->oxuser__oxfname->value,
                'surname' => $user->oxuser__oxlname->value
            ];
            $invoiceData->email = $user->oxuser__oxusername->value;
            $invoiceData->birth_date = Registry::getSession()->getVariable('pp_birth_date');
            $invoiceData->phone = [
                'national_number' =>  Registry::getSession()->getVariable('pp_phone_number'),
                'country_code' =>  Registry::getSession()->getVariable('pp_phone_country_code')
            ];

            $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
            $country->load($user->oxuser__oxcountryid->value);

            $address = new ShippingDetailAddress();
            $address->country_code = $country->oxcountry__oxisoalpha2->value;
            $address->address_line_1 = $user->oxuser__oxstreet->value . ' ' . $user->oxuser__oxstreetnr->value;
            $address->postal_code = $user->oxuser__oxzip->value;
            $address->admin_area_2 = $user->oxuser__oxcity->value;

            $invoiceData->billing_address = $address;

            $shop = Registry::getConfig()->getActiveShop();
            $invoiceData->experience_context = [
                'locale' => 'de-DE',
                'brand_name' => $shop->oxshops__oxname->value,
                'logo_url' => Registry::getConfig()->getImageUrl(false, true). 'logo_oxid.png',
                'customer_service_instructions' => [
                    $shop->oxshops__oxtelefon->value
                ]
            ];

            $source->pay_upon_invoice = $invoiceData;
            return $source;
        } elseif (self::isAPM($payment)) {
            $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
            $country->load($user->oxuser__oxcountryid->value);

            //TODO: check if this is correct for all possible payment methods
            $source = new PaymentSource();
            $data = new PaymentSourceData($user->oxuser__oxfname . ' ' . $user->oxuser__oxlname, $country->oxcountry__oxisoalpha2->value);
            $key = strtolower($method);
            $source->$key = $data;

            return $source;
        }
    }

    public static function createShipping($user, $basket, $shippingPreference, $countryId = null)
    {
        $shipping = new ShippingDetail();

        if ($user) {
            $deliveryAddress = self::getDelAddressInfo(); //I would rather not get this from a global, but this is oxid style
            $address = new ShippingDetailAddress();

            if ($deliveryAddress) {
                $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
                $country->load($deliveryAddress->oxaddress__oxcountryid->value);

                $shipping->setName(new ShippingDetailName($deliveryAddress->oxaddress__oxfname->value. ' ' . $deliveryAddress->oxaddress__oxlname->value));
                $address->country_code = $country->oxcountry__oxisoalpha2->value;
                $address->address_line_1 = $deliveryAddress->oxaddress__oxstreet->value . ' ' . $deliveryAddress->oxaddress__oxstreetnr->value;
                $address->postal_code = $deliveryAddress->oxaddress__oxzip->value;
                $address->admin_area_2 = $deliveryAddress->oxaddress__oxcity->value;
            } else {
                $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
                $country->load($user->oxuser__oxcountryid->value);

                $shipping->setName(new ShippingDetailName($user->oxuser__oxfname->value. ' ' . $user->oxuser__oxlname->value));
                $address->country_code = $country->oxcountry__oxisoalpha2->value;
                $address->address_line_1 = $user->oxuser__oxstreet->value . ' ' . $user->oxuser__oxstreetnr->value;
                $address->postal_code = $user->oxuser__oxzip->value;
                $address->admin_area_2 = $user->oxuser__oxcity->value;
            }

            $shipping->setAddress($address);
        }

        if ($shippingPreference === ApplicationContext::SHIPPING_PREFERENCE_GET_FROM_FILE) {
            $shipping->setOptions(self::getShippingOptions($user, $basket, $countryId));
        }

        return $shipping;
    }

    private static function isPayUponInvoice($payment)
    {
        $method = $payment->oxpayments__agpaypalpaymentmethod->value;
        return PaymentSource::isPUI($method);
    }

    private static function isAPM($payment)
    {
        $method = $payment->oxpayments__agpaypalpaymentmethod->value;
        return PaymentSource::isAPM($method);
    }

    private static function getShippingOptions($user, $basket, $countryId = null)
    {
        if (!self::$shippingOptions) {
            $options = [];
            $initialCountryId = $countryId;
            $oCurrency = Registry::getConfig()->getActShopCurrencyObject();

            $currencyName = $basket->getBasketCurrency()->name;

            /*if (!$countryId) {
                $countryId = current(Registry::getConfig()->getConfigParam('aHomeCountry'));
            }*/

            if (!$user) {
                $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
                $user->setId();
                $user->setIsAnonymousPayPalUser(true);
                $basket->setBasketUser($user);
                if (!$countryId) {
                    $countryId = current(Registry::getConfig()->getConfigParam('aHomeCountry'));
                }
            }

            if ($countryId) {
                $user->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field($countryId);
                $user->forceActiveCountry($countryId);
                Registry::getConfig()->setGlobalParameter('delcountryid', $countryId);
            }

            $sActShipSet = Registry::getConfig()->getRequestParameter('sShipSet');
            if (!$sActShipSet) {
                $sActShipSet = Registry::getSession()->getVariable('sShipSet');
            }
            // load sets, active set, and active set payment list
            list($aAllSets, $sActShipSet) = Registry::get(\OxidEsales\Eshop\Application\Model\DeliverySetList::class)->getDeliverySetData($sActShipSet, $user, $basket);

            foreach ($aAllSets as $deliverySet) {
                $costs = $basket->getDeliveryCostForShipset($deliverySet->getId());
                $option = new ShippingDetailOption();
                //if ($initialCountryId)
                //    $option->setAmount(new Money($currencyName, $costs->getBruttoPrice()));

                $option->setId($deliverySet->getId());
                $option->setLabel($deliverySet->oxdeliveryset__oxtitle->value . ' - ' . Registry::getLang()->formatCurrency($costs->getBruttoPrice()) . ' ' . $oCurrency->sign);
                $option->setType('SHIPPING');
                $option->setSelected($deliverySet->getId() === $sActShipSet);
                $options[] = $option;
            }

            self::$shippingOptions = $options;
        }
        return self::$shippingOptions;
    }

    private static function getSelectedShippingOption($user, $basket, $countryId = null)
    {
        $options = self::getShippingOptions($user, $basket, $countryId);
        return current(array_filter($options, function ($option) {
            return $option->selected;
        }));
    }
}
