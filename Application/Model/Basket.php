<?php

namespace Aggrosoft\PayPal\Application\Model;

class Basket extends Basket_parent
{
    public function getDeliveryCostForShipset($shipsetId) {
        $currentId = $this->getShippingId();
        $currentDeliveryPrice = $this->_oDeliveryPrice;

        $this->_oDeliveryPrice = null;
        $this->setShipping($shipsetId);
        $deliveryCosts = $this->_calcDeliveryCost();

        $this->setShipping($currentId);
        $this->_oDeliveryPrice = $currentDeliveryPrice;

        return $deliveryCosts;
    }
}