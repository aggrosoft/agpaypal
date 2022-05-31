<?php

namespace Aggrosoft\PayPal\Application\Core;

class FraudNet
{
    public static function getSessionIdentifier ()
    {
        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        return substr($session->getId(), 0, 32);
    }

    public static function getSourceWebsiteIdentifier ()
    {
        $config = \OxidEsales\Eshop\Core\Registry::getConfig();
        return md5($config->getShopUrl().$config->getRequestControllerId());
    }
}