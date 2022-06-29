<?php

namespace Aggrosoft\PayPal\Application\Component;

use Aggrosoft\PayPal\Application\Core\PayPalBasketHandler;
use OxidEsales\Eshop\Core\Registry;

class BasketComponent extends BasketComponent_parent
{
    public function toBasket($sProductId = null, $dAmount = null, $aSel = null, $aPersParam = null, $blOverride = false)
    {
        $this->destroyPayPalToken();
        return parent::toBasket($sProductId, $dAmount, $aSel, $aPersParam, $blOverride);
    }

    public function changeBasket(
        $sProductId = null,
        $dAmount = null,
        $aSel = null,
        $aPersParam = null,
        $blOverride = true
    ) {
        $this->destroyPayPalToken();
        return parent::changeBasket($sProductId, $dAmount, $aSel, $aPersParam, $blOverride);
    }

    protected function destroyPayPalToken()
    {
        $token = Registry::getSession()->getVariable('pptoken');
        if ($token) {
            PayPalBasketHandler::destroyUserBasketForToken($token);
            Registry::getSession()->setVariable('pptoken', '');
        }
    }
}
