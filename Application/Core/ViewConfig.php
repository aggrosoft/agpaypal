<?php

namespace Aggrosoft\PayPal\Application\Core;

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
}
