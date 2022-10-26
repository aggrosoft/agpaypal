<?php

namespace Aggrosoft\PayPal\Application\Model;

use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;

/**
 * Class PaymentList
 * @package Aggrosoft\PayPal\Application\Model
 * Disallow PayPal PUI for companys
 */
class PaymentList extends PaymentList_parent
{
    public function getPaymentList($sShipSetId, $dPrice, $oUser = null)
    {
        parent::getPaymentList($sShipSetId, $dPrice, $oUser);
        if ($oUser && trim($oUser->oxuser__oxcompany->value)) {
            foreach($this as $payment) {
                if ($payment->oxpayments__agpaypalpaymentmethod->value === PaymentSource::PAY_UPON_INVOICE) {
                    $this->offsetUnset($payment->getId());
                }
            }
        }
        return $this->_aArray;
    }
}
