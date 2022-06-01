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
     * @return CreateOrderRequest
     */
    public static function create($user, $basket, $payment, $returnUrl)
    {
        //Cache this, used multiple times
        self::$shippingOptions = null;

        $request = new CreateOrderRequest();
        $request->setIntent(CreateOrderRequest::INTENT_CAPTURE);
        $request->setProcessingInstruction(self::isPayUponInvoice($payment) ? CreateOrderRequest::PROCESSING_INSTRUCTION_ORDER_COMPLETE_ON_PAYMENT_APPROVAL : CreateOrderRequest::PROCESSING_INSTRUCTION_NO_INSTRUCTION);
        if ($user) {
            $request->setPayer(self::createPayer($user));
        }
        $request->addPurchaseUnit(self::createPurchaseUnitRequest($user, $basket));
        $request->setApplicationContext(self::createApplicationContext($user, $returnUrl));
        $request->setPaymentSource(self::createPaymentSource($user, $payment));
        if (self::isPayUponInvoice($payment)) {
            $request->setMetadataId(FraudNet::getSessionIdentifier());
        }
        return $request;
    }

    public static function createPurchaseUnitRequest($user, $basket, $countryId = null)
    {
        $config = Registry::getConfig();
        $currencyName = $basket->getBasketCurrency()->name;
        $vats = $basket->getProductVats(false);

        $shippingOption = self::getSelectedShippingOption($user, $basket, $countryId);

        $basket->setShipping($shippingOption->id);
        $basket->calculateBasket(true);

        $deliveryCosts = $basket->getDeliveryCost();

        $amountBreakDown = new AmountBreakdown();
        $amountBreakDown->item_total = new Money($currencyName, $basket->getNettoSum());
        $amountBreakDown->tax_total = new Money($currencyName, array_sum($vats));
        $amountBreakDown->shipping = new Money($currencyName, $deliveryCosts->getBruttoPrice());
        $amountBreakDown->discount = new Money($currencyName, $basket->getTotalDiscountSum());

        $unit = new PurchaseUnitRequest();
        $unit->setAmount(new AmountWithBreakdown($currencyName, $basket->getPrice()->getBruttoPrice(), $amountBreakDown));
        $unit->setPayee(new Payee($config->getShopConfVar("sPayPalEmailAddress", null, "module:agpaypal")));
        $unit->setShipping(self::createShipping($user, $basket, $countryId));

        $items = [];

        foreach ($basket->getContents() as $basketItem) {
            $article = $basketItem->getArticle();
            $item = new Item();
            $item->category = $article->oxarticles__oxnonmaterial->value ? 'DIGITAL_GOODS' : 'PHYSICAL_GOODS';
            $item->name = $basketItem->getTitle();
            $item->quantity = $basketItem->getAmount();
            $item->unit_amount = new Money($currencyName, $basketItem->getUnitPrice()->getNettoPrice());
            $item->tax = new Money($currencyName, $basketItem->getUnitPrice()->getVatValue());
            $item->tax_rate = $basketItem->getUnitPrice()->getVat();
            $items[] = $item;
        }

        $unit->setItems($items);

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
        }else{
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
            $oDelAdress->load($soxAddressId);
        }

        return $oDelAdress;
    }

    public static function createApplicationContext($user, $returnUrl)
    {
        $config = Registry::getConfig();
        $shop = $config->getActiveShop();

        $context = new ApplicationContext();
        $context->setBrandName($shop->oxshops__oxname->value);
        $context->setLandingPage(ApplicationContext::LANDING_PAGE_NO_PREFERENCE);
        $context->setShippingPreference($user ? ApplicationContext::SHIPPING_PREFERENCE_SET_PROVIDED_ADDRESS : ApplicationContext::SHIPPING_PREFERENCE_GET_FROM_FILE);
        $context->setUserAction(ApplicationContext::USER_ACTION_CONTINUE);
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
                'logo_url' => Registry::getConfig()->getImageUrl(false,true). 'logo_oxid.png',
                'customer_service_instructions' => [
                    $shop->oxshops__oxtelefon->value
                ]
            ];

            $source->pay_upon_invoice = $invoiceData;
            return $source;
        }elseif ($method && $method !== PaymentSource::PAYPAL && $method !== PaymentSource::CARD){
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

    public static function createShipping ($user, $basket, $countryId = null)
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
            }else{
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

        $shipping->setOptions(self::getShippingOptions($user, $basket, $countryId));

        return $shipping;
    }

    private static function isPayUponInvoice ($payment)
    {
        $method = $payment->oxpayments__agpaypalpaymentmethod->value;
        return $method === PaymentSource::PAY_UPON_INVOICE;
    }

    private static function getShippingOptions ($user, $basket, $countryId = null)
    {
        if (!self::$shippingOptions) {
            $options = [];

            $currencyName = $basket->getBasketCurrency()->name;

            if (!$countryId) {
                $countryId = current(Registry::getConfig()->getConfigParam('aHomeCountry'));
            }

            if (!$user) {
                $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
                $user->setId();
                $user->oxuser__oxcountryid = new \OxidEsales\Eshop\Core\Field($countryId);
            }

            $sActShipSet = Registry::getConfig()->getRequestParameter('sShipSet');
            if (!$sActShipSet) {
                $sActShipSet = Registry::getSession()->getVariable('sShipSet');
            }
            // load sets, active set, and active set payment list
            list($aAllSets, $sActShipSet, $aActPaymentList) = Registry::get(\OxidEsales\Eshop\Application\Model\DeliverySetList::class)->getDeliverySetData($sActShipSet, $user, $basket);

            foreach($aAllSets as $deliverySet) {
                $costs = $basket->getDeliveryCostForShipset($deliverySet->getId());
                $option = new ShippingDetailOption();
                $option->setAmount(new Money($currencyName,$costs->getBruttoPrice()));
                $option->setId($deliverySet->getId());
                $option->setLabel($deliverySet->oxdeliveryset__oxtitle->value);
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
        return current(array_filter($options, function($option){ return $option->selected; }));
    }
}