<?php

namespace Aggrosoft\PayPal\Application\Model;

class VatSelector extends VatSelector_parent
{
    protected $_sForceVatCountry;

    public function setForceVatCountry ($sCountryId)
    {
        $this->_sForceVatCountry = $sCountryId;
    }

    protected function _getVatCountry(\OxidEsales\Eshop\Application\Model\User $oUser)
    {
        if ($this->_sForceVatCountry) {
            return $this->_sForceVatCountry;
        }

        return parent::_getVatCountry($oUser);
    }

    public function getUserVat(\OxidEsales\Eshop\Application\Model\User $oUser, $blCacheReset = false)
    {
        if ($this->_sForceVatCountry) {
            return parent::getUserVat($oUser, true);
        }

        return parent::getUserVat($oUser, $blCacheReset);
    }
}