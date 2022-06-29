<?php

namespace Aggrosoft\PayPal\Application\Model;

class DeliveryList extends DeliveryList_parent
{
    public function getDeliveryList($oBasket, $oUser = null, $sDelCountry = null, $sDelSet = null)
    {
        // Fix for oxid < 6.3
        $this->_aDeliveries = [];
        return parent::getDeliveryList($oBasket, $oUser, $sDelCountry, $sDelSet);
    }
}
