<?php

namespace Aggrosoft\PayPal\Application\Model;

class UserBasket extends UserBasket_parent
{
    /**
     * User baskets to do not store chosen wrap id currently, fix this
     * @param $sProductId
     * @param $dAmount
     * @param $aSel
     * @param $blOverride
     * @param $aPersParam
     * @param $sWrapId
     * @return void|null
     */
    public function addPayPalItemToBasket($sProductId = null, $dAmount = null, $aSel = null, $blOverride = false, $aPersParam = null, $sWrapId = null)
    {
        // basket info is only written in DB when something is in it
        if ($this->_blNewBasket) {
            $this->save();
        }

        if (($oUserBasketItem = $this->getItem($sProductId, $aSel, $aPersParam))) {
            $oUserBasketItem->oxuserbasketitems__agpaypalwrapid = new \OxidEsales\Eshop\Core\Field($sWrapId);

            // updating object info and adding (if not yet added) item into basket items array
            if (!$blOverride && !empty($oUserBasketItem->oxuserbasketitems__oxamount->value)) {
                $dAmount += $oUserBasketItem->oxuserbasketitems__oxamount->value;
            }

            if (!$dAmount) {
                // amount = 0 removes the item
                $oUserBasketItem->delete();
                if (isset($this->_aBasketItems[$this->_getItemKey($sProductId, $aSel, $aPersParam)])) {
                    unset($this->_aBasketItems[$this->_getItemKey($sProductId, $aSel, $aPersParam)]);
                }
            } else {
                $oUserBasketItem->oxuserbasketitems__oxamount = new \OxidEsales\Eshop\Core\Field($dAmount, \OxidEsales\Eshop\Core\Field::T_RAW);
                $oUserBasketItem->save();

                $this->_aBasketItems[$this->_getItemKey($sProductId, $aSel, $aPersParam)] = $oUserBasketItem;
            }

            //update timestamp
            $this->oxuserbaskets__oxupdate = new \OxidEsales\Eshop\Core\Field(\OxidEsales\Eshop\Core\Registry::getUtilsDate()->getTime());
            $this->save();

            return $dAmount;
        }
    }

    public function getBasketUser()
    {
        $user = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
        if ($user->load($this->oxuserbaskets__oxuserid->value)) {
            return $user;
        }
    }
}
