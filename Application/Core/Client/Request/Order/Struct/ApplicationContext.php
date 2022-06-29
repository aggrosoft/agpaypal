<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct;

use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class ApplicationContext extends RequestObject
{
    public const LANDING_PAGE_LOGIN = 'LOGIN';
    public const LANDING_PAGE_BILLING = 'BILLING';
    public const LANDING_PAGE_NO_PREFERENCE = 'NO_PREFERENCE';

    public const SHIPPING_PREFERENCE_GET_FROM_FILE = 'GET_FROM_FILE';
    public const SHIPPING_PREFERENCE_NO_SHIPPING = 'NO_SHIPPING';
    public const SHIPPING_PREFERENCE_SET_PROVIDED_ADDRESS = 'SET_PROVIDED_ADDRESS';

    public const USER_ACTION_CONTINUE = 'CONTINUE';
    public const USER_ACTION_PAY_NOW = 'PAY_NOW';

    /**
     * @var string
     */
    public $brand_name;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var string
     */
    public $landing_page;

    /**
     * @var string
     */
    public $shipping_preference;

    /**
     * @var string
     */
    public $user_action;

    /**
     * @var PaymentMethod
     */
    public $payment_method;

    /**
     * @var string
     */
    public $return_url;

    /**
     * @var string
     */
    public $cancel_url;

    /**
     * @var object
     */
    public $stored_payment_source;

    /**
     * @return string
     */
    public function getBrandName(): string
    {
        return $this->brand_name;
    }

    /**
     * @param string $brand_name
     */
    public function setBrandName(string $brand_name)
    {
        $this->brand_name = $brand_name;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getLandingPage(): string
    {
        return $this->landing_page;
    }

    /**
     * @param string $landing_page
     */
    public function setLandingPage(string $landing_page)
    {
        $this->landing_page = $landing_page;
    }

    /**
     * @return string
     */
    public function getShippingPreference(): string
    {
        return $this->shipping_preference;
    }

    /**
     * @param string $shipping_preference
     */
    public function setShippingPreference(string $shipping_preference)
    {
        $this->shipping_preference = $shipping_preference;
    }

    /**
     * @return string
     */
    public function getUserAction(): string
    {
        return $this->user_action;
    }

    /**
     * @param string $user_action
     */
    public function setUserAction(string $user_action)
    {
        $this->user_action = $user_action;
    }

    /**
     * @return PaymentMethod
     */
    public function getPaymentMethod(): PaymentMethod
    {
        return $this->payment_method;
    }

    /**
     * @param PaymentMethod $payment_method
     */
    public function setPaymentMethod(PaymentMethod $payment_method)
    {
        $this->payment_method = $payment_method;
    }

    /**
     * @return string
     */
    public function getReturnUrl(): string
    {
        return $this->return_url;
    }

    /**
     * @param string $return_url
     */
    public function setReturnUrl(string $return_url)
    {
        $this->return_url = $return_url;
    }

    /**
     * @return string
     */
    public function getCancelUrl(): string
    {
        return $this->cancel_url;
    }

    /**
     * @param string $cancel_url
     */
    public function setCancelUrl(string $cancel_url)
    {
        $this->cancel_url = $cancel_url;
    }

    /**
     * @return object
     */
    public function getStoredPaymentSource()
    {
        return $this->stored_payment_source;
    }

    /**
     * @param object $stored_payment_source
     */
    public function setStoredPaymentSource($stored_payment_source)
    {
        $this->stored_payment_source = $stored_payment_source;
    }
}
