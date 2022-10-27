<?php

namespace Aggrosoft\PayPal\Application\Core;

use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;

class ViewConfig extends ViewConfig_parent
{
    public function getFraudNetSessionIdentifier()
    {
        return PayPalHelper::getFraudNetSessionIdentifier();
    }

    public function getFraudNetSourceWebsiteIdentifier()
    {
        return PayPalHelper::getFraudNetSourceWebsiteIdentifier();
    }

    public function isPayPalSandbox()
    {
        return PayPalHelper::isPayPalSandbox();
    }

    public function getPayPalClientId()
    {
        return PayPalHelper::getPayPalClientId();
    }

    public function getPayPalPaymentId()
    {
        return PayPalHelper::getPayPalPaymentId();
    }

    public function getPayPalLocale()
    {
        $abbr = $this->getActLanguageAbbr();
        return PayPalCountryMap::mapIsoCodeToLocale(strtoupper($abbr));
    }

    public function showPayPalButtonInDetails()
    {
        return PayPalHelper::showPayPalButtonInDetails();
    }

    public function showPayPalButtonInBasket()
    {
        return PayPalHelper::showPayPalButtonInBasket();
    }

    public function showPayPalMessageInDetails()
    {
        return PayPalHelper::showPayPalMessageInDetails();
    }

    public function showPayPalMessageInBasket()
    {
        return PayPalHelper::showPayPalMessageInBasket();
    }

    public function loadPayPalPaymentCSS()
    {
        return PayPalHelper::showPayPalMessageInBasket();
    }

    public function getPayPalButtonStyleDetails()
    {
        return json_encode($this->preparePayPalButtonStyle(PayPalHelper::getPayPalButtonStyleDetails()));
    }

    public function getPayPalButtonStyleBasket()
    {
        return json_encode($this->preparePayPalButtonStyle(PayPalHelper::getPayPalButtonStyleBasket()));
    }

    public function getPayPalButtonStyleOrder()
    {
        return json_encode($this->preparePayPalButtonStyle(PayPalHelper::getPayPalButtonStyleOrder()));
    }

    public function getPayPalDisabledFundings()
    {
        return implode(',', $this->prepareFundings(PayPalHelper::getPayPalDisabledFundings()));
    }

    public function getPayPalEnabledFundings()
    {
        return implode(',', $this->prepareFundings(PayPalHelper::getPayPalEnabledFundings()));
    }

    private function preparePayPalButtonStyle ($style) {
        if (isset($style['height'])){
            $style['height'] = intval($style['height']);
            $style['height'] = max($style['height'], 25);
            $style['height'] = min($style['height'], 55);
        }
        if (isset($style['tagline'])){
            $style['tagline'] = boolval($style['tagline']);
        }
        if (isset($style['layout']) && $style['layout'] !== 'horizontal'){
            $style['tagline'] = false;
        }
        return $style;
    }

    private function prepareFundings($fundings) {
        return array_filter($fundings, function($funding) {
            return PaymentSource::isValidFunding($funding);
        });
    }
}
