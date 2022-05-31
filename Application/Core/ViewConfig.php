<?php

namespace Aggrosoft\PayPal\Application\Core;

class ViewConfig extends ViewConfig_parent
{
    public function getFraudNetSessionIdentifier ()
    {
        return FraudNet::getSessionIdentifier();
    }

    public function getFraudNetSourceWebsiteIdentifier ()
    {
        return FraudNet::getSourceWebsiteIdentifier();
    }

    public function isPayPalSandbox ()
    {
        return \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('blPayPalSandboxMode', null, 'module:agpaypal');
    }
}