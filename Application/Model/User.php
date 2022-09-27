<?php

namespace Aggrosoft\PayPal\Application\Model;

class User extends User_parent
{
    protected $_blAnonymousPayPalUser = false;

    public function setIsAnonymousPayPalUser($blAnonymousPayPalUser)
    {
        $this->_blAnonymousPayPalUser = $blAnonymousPayPalUser;
    }

    public function getIsAnonymousPayPalUser()
    {
        return $this->_blAnonymousPayPalUser;
    }

    public function getUserGroups($sOXID = null)
    {
        if ($this->getIsAnonymousPayPalUser()) {
            $groups = oxNew('oxList', 'oxgroups');
            $sViewName = getViewName("oxgroups");
            $sSelect = "select {$sViewName}.* from {$sViewName} where {$sViewName}.oxid = 'oxidnotyetordered'";
            $groups->selectString($sSelect);
            return $groups;
        } else {
            return parent::getUserGroups($sOXID);
        }
    }

}
