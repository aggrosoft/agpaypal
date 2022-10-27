<?php

namespace Aggrosoft\PayPal\Application\Model;

class UserAddressList extends UserAddressList_parent
{
    public function load($sUserId)
    {
        $sViewName = getViewName('oxcountry');
        $oBaseObject = $this->getBaseObject();
        $sSelectFields = $oBaseObject->getSelectFields();

        $sSelect = "
                SELECT {$sSelectFields}, `oxcountry`.`oxtitle` AS oxcountry
                FROM oxaddress
                LEFT JOIN {$sViewName} AS oxcountry ON oxaddress.oxcountryid = oxcountry.oxid
                WHERE (oxaddress.agpaypalhash IS NULL or oxaddress.agpaypalhash = '') AND oxaddress.oxuserid = " . \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->quote($sUserId);
        $this->selectString($sSelect);
    }
}
