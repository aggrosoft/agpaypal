<?php

namespace Aggrosoft\PayPal\Application\Model;

use Aggrosoft\PayPal\Application\Core\Client\Exception\RestException;
use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\UpdateOrderInvoiceNumberRequest;
use Aggrosoft\PayPal\Application\Core\Factory\Request\Order\CapturePaymentRequestFactory;
use Aggrosoft\PayPal\Application\Core\PayPalInitiator;
use OxidEsales\Eshop\Core\Registry;

class PaymentGateway extends PaymentGateway_parent
{
    public function executePayment($dAmount, &$oOrder)
    {
        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payment->load($this->_oPaymentInfo->oxuserpayments__oxpaymentsid->value);

        if ($payment->oxpayments__agpaypalpaymentmethod->value) {

            if ($payment->oxpayments__agpaypalpaymentmethod->value === PaymentSource::PAY_UPON_INVOICE) {
                $paypal = new PayPalInitiator();
                try {
                    $paypal->initiate(Registry::getConfig()->getCurrentShopUrl() . 'index.php?cl=order&fnc=execute');
                } catch(RestException $re) {
                    $this->_iLastErrorNo = null; // $re->getCode();
                    $this->_sLastError = null; // Registry::getLang()->translateString($re->getMessage());
                    \OxidEsales\Eshop\Core\Registry::getUtilsView()->addErrorToDisplay($re);
                    return false;
                } catch (\Exception $e) {
                    $this->_iLastErrorNo = 907;
                    $this->_sLastError = Registry::getLang()->translateString('ERR_PAYPAL_ORDER_CREATE_FAILED');
                    return false;
                }
            }

            $token = Registry::getSession()->getVariable('pptoken');

            if ($token) {

                $client = new PayPalRestClient();

                // Send order number to paypal
                $oOrder->setOrderNumber();
                $request = new UpdateOrderInvoiceNumberRequest($token, $oOrder->oxorder__oxordernr->value);

                try {
                    $client->execute($request);
                } catch (\Exception $e) {
                    $this->_iLastErrorNo = 905;
                    $this->_sLastError = 'ERR_PAYPAL_ORDER_UPDATE_FAILED';
                    return false;
                }

                // Now capture payment if needed
                if ($payment->oxpayments__agpaypalpaymentmethod->value === PaymentSource::PAYPAL || $payment->oxpayments__agpaypalpaymentmethod->value === PaymentSource::CARD ) {
                    $request = CapturePaymentRequestFactory::create($token);

                    try {
                        $response = $client->execute($request);
                    } catch (\Exception $e) {
                        $this->_iLastErrorNo = 902;
                        $this->_sLastError = 'ERR_PAYPAL_CAPTURE_FAILED';
                        return false;
                    }

                    $capture = $response->purchase_units[0]->payments->captures[0];

                    if (!$capture || $capture->status === 'DENIED') {
                        $this->_iLastErrorNo = 903;
                        $this->_sLastError = 'ERR_PAYPAL_CAPTURE_DENIED';
                        return false;
                    }elseif ($capture->status === 'COMPLETED') {
                        $oOrder->oxorder__oxpaid = new \OxidEsales\Eshop\Core\Field(date("Y-m-d H:i:s"));
                    }

                    $oOrder->oxorder__agpaypalcaptureid = new \OxidEsales\Eshop\Core\Field($capture->id);
                    $oOrder->oxorder__agpaypaltransstatus = new \OxidEsales\Eshop\Core\Field($capture->status);
                }

                $oOrder->oxorder__oxtransid = new \OxidEsales\Eshop\Core\Field($token);
                $oOrder->save();

                $this->_iLastErrorNo = null;
                $this->_sLastError = null;
                return true;

            } else {
                $this->_iLastErrorNo = 901;
                $this->_sLastError = 'ERR_PAYPAL_TOKEN_MISSING';
                return false;
            }
        } else {
            return parent::executePayment($dAmount, $oOrder);
        }
    }
}