<?php

namespace Aggrosoft\PayPal\Application\Model;

use OxidEsales\Eshop\Core\Registry;

class Basket extends Basket_parent
{
    protected $_blDisableArtStockInBasket = false;

    public function getDeliveryCostForShipset($shipsetId)
    {
        Registry::getConfig()->setConfigParam('blCalculateDelCostIfNotLoggedIn', true);
        Registry::getConfig()->setConfigParam('bl_perfLoadDelivery', true);

        $currentId = $this->getShippingId();
        $currentDeliveryPrice = $this->_oDeliveryPrice;

        $this->_oDeliveryPrice = null;
        $this->setShipping($shipsetId);
        $deliveryCosts = $this->_calcDeliveryCost();

        $this->setShipping($currentId);
        $this->_oDeliveryPrice = $currentDeliveryPrice;

        return $deliveryCosts;
    }

    public function setDisableArtStockInBasket($blDisableArtStockInBasket)
    {
        $this->_blDisableArtStockInBasket = $blDisableArtStockInBasket;
    }

    public function getDisableArtStockInBasket()
    {
        return $this->_blDisableArtStockInBasket;
    }

    public function getArtStockInBasket($sArtId, $sExpiredArtId = null)
    {
        if ($this->getDisableArtStockInBasket()) {
            return 0;
        } else {
            return parent::getArtStockInBasket($sArtId, $sExpiredArtId);
        }
    }

    // Let method throw errors
    public function addRestoredVoucherForPayPal($sVoucherId)
    {
        $dPrice = 0;
        if ($this->_oDiscountProductsPriceList) {
            $dPrice = $this->_oDiscountProductsPriceList->getSum($this->isCalculationModeNetto());
        }

        $oVoucher = oxNew(\OxidEsales\Eshop\Application\Model\Voucher::class);

        if (!$this->_blSkipVouchersAvailabilityChecking) {
            $oVoucher->getVoucherByNr($sVoucherId, $this->_aVouchers, true);
            $oVoucher->checkVoucherAvailability($this->_aVouchers, $dPrice);
            $oVoucher->checkUserAvailability($this->getBasketUser());
            $oVoucher->markAsReserved();
        } else {
            $oVoucher->load($sVoucherId);
        }

        // saving voucher info
        $this->_aVouchers[$oVoucher->oxvouchers__oxid->value] = $oVoucher->getSimpleVoucher();


        $this->onUpdate();
    }
}
