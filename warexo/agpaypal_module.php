<?php

class AgPayPalModule
{
    public function get_paypal_details($orderid)
    {
        $order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $order->load($orderid);
        $bankData = $order->getPayPalBankData();

        if ($bankData) {
            $result = new \stdClass();
            $result->accountHolder = $bankData->agpaypalbankdata__accountholder->value;
            $result->accountNumber = $bankData->agpaypalbankdata__iban->value;
            $result->bankCode = $bankData->agpaypalbankdata__bic->value;
            $result->bankName = $bankData->agpaypalbankdata__bankname->value;
            $result->invoiceReference = $bankData->agpaypalbankdata__reference->value;
            $result->duedate = date('d.m.Y', strtotime('+' . $this->getPaymentTerm() . ' day', strtotime($order->oxorder__oxorderdate->value)));
            return $result;
        }
    }

    public function get_additional_fields($table, $data, $entity=null)
    {
        if ($table === 'oxorder' && $entity && $entity->oxorder__agpaypalcaptureid->value) {
            $data['paymentTransactionId'] = $entity->oxorder__agpaypalcaptureid->value;
        }
        return $data;
    }

    protected function getPaymentTerm()
    {
        if (null === $iPaymentTerm = \OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('iPaymentTerm')) {
            $iPaymentTerm = 7;
        }

        return $iPaymentTerm;
    }
}

$paypalModule = new AgPayPalModule();
ModuleManager::getInstance()->registerModule($paypalModule);
