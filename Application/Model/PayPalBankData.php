<?php

namespace Aggrosoft\PayPal\Application\Model;

class PayPalBankData extends \OxidEsales\Eshop\Core\Model\BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->init('agpaypalbankdata');
    }

    public function assignPayPalPUIData($orderId, $data)
    {
        if ($data->payment_reference) {
            $this->agpaypalbankdata__oxorderid = new \OxidEsales\Eshop\Core\Field($orderId);
            $this->agpaypalbankdata__reference = new \OxidEsales\Eshop\Core\Field($data->payment_reference);
            $this->agpaypalbankdata__bic = new \OxidEsales\Eshop\Core\Field($data->deposit_bank_details->bic);
            $this->agpaypalbankdata__bankname = new \OxidEsales\Eshop\Core\Field($data->deposit_bank_details->bank_name);
            $this->agpaypalbankdata__iban = new \OxidEsales\Eshop\Core\Field($data->deposit_bank_details->iban);
            $this->agpaypalbankdata__accountholder = new \OxidEsales\Eshop\Core\Field($data->deposit_bank_details->account_holder_name);
            return true;
        }
        return false;
    }
}
