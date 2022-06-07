<?php

namespace Aggrosoft\PayPal\Application\Model;

class InvoicepdfArticleSummary extends InvoicepdfArticleSummary_parent
{
    protected function _setPayUntilInfo(&$iStartPos)
    {
        parent::_setPayUntilInfo($iStartPos);

        $ppBankData = $this->_oData->getPayPalBankData();

        if ($ppBankData) {
            $iStartPos += 4;
            $this->addPayPalBankInfoLine($iStartPos, $this->_oData->translate('PAYPAL_INVOICE_PDF_PAYTRANSFER'));
            $iStartPos += 4;
            $this->addPayPalBankInfoLine($iStartPos, $this->_oData->translate('PAYPAL_INVOICE_PDF_ACCOUNTHOLDER'). ' ' . $ppBankData->agpaypalbankdata__accountholder->value);
            $this->addPayPalBankInfoLine($iStartPos, $this->_oData->translate('PAYPAL_INVOICE_PDF_BANKNAME'). ' ' . $ppBankData->agpaypalbankdata__bankname->value);
            $this->addPayPalBankInfoLine($iStartPos, $this->_oData->translate('PAYPAL_INVOICE_PDF_IBAN'). ' ' . $ppBankData->agpaypalbankdata__iban->value);
            $this->addPayPalBankInfoLine($iStartPos, $this->_oData->translate('PAYPAL_INVOICE_PDF_BIC'). ' ' . $ppBankData->agpaypalbankdata__bic->value);
            $this->addPayPalBankInfoLine($iStartPos, $this->_oData->translate('PAYPAL_INVOICE_PDF_REFERENCE'). ' ' . $ppBankData->agpaypalbankdata__reference->value);
            $iStartPos += 4;
            $this->addPayPalBankInfoLine($iStartPos, $this->_oData->translate('PAYPAL_INVOICE_PDF_ADDITIONALINFO_1'));
            $this->addPayPalBankInfoLine($iStartPos, $this->_oData->translate('PAYPAL_INVOICE_PDF_ADDITIONALINFO_2'));
            $this->addPayPalBankInfoLine($iStartPos, $this->_oData->translate('PAYPAL_INVOICE_PDF_ADDITIONALINFO_3'));
            $this->addPayPalBankInfoLine($iStartPos, $this->_oData->translate('PAYPAL_INVOICE_PDF_ADDITIONALINFO_4'));
        }
    }

    protected function addPayPalBankInfoLine(&$iStartPos, $text)
    {
        $this->font($this->getFont(), '', 10);
        $this->text(15, $iStartPos + 4, $text);
        $iStartPos += 4;
    }
}