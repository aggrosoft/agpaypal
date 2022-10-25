<?php

namespace Aggrosoft\PayPal\Application\Model;

use Aggrosoft\PayPal\Application\Core\Client\Exception\RestException;
use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\UpdateOrderDetailsRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\Payments\Captures\RefundCapturedPaymentRequest;
use Aggrosoft\PayPal\Application\Core\Factory\Request\Order\CapturePaymentRequestFactory;
use Aggrosoft\PayPal\Application\Core\PayPalInitiator;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\Eshop\Core\Registry;

class Order extends Order_parent
{
    protected $_blValidateDeliveryAddressMD5 = true;
    protected $_blSkipOrderMails = false;

    public function cancelOrder()
    {
        parent::cancelOrder();

        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payment->load($this->oxorder__oxpaymenttype->value);

        if ($payment->oxpayments__agpaypalpaymentmethod->value && $this->oxorder__agpaypalcaptureid->value) {
            $client = new PayPalRestClient();
            $request = new RefundCapturedPaymentRequest($this->oxorder__agpaypalcaptureid->value);
            $response = $client->execute($request);

            $this->oxorder__agpaypalrefundid = new \OxidEsales\Eshop\Core\Field($response->id);
            $this->oxorder__agpaypaltransstatus = new \OxidEsales\Eshop\Core\Field('REFUNDED');
            $this->save();
        }
    }

    protected function _sendOrderByEmail($oUser = null, $oBasket = null, $oUserPayment = null)
    {
        $oPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $oPayment->load($oUserPayment->oxuserpayments__oxpaymentsid->value);

        if ($this->_blSkipOrderMails) {
            // Mail will be sent in capture webhook as we need bank data and capturing state
            $this->_oUser = $oUser;
            $this->_oBasket = $oBasket;
            $this->_oPayment = $oUserPayment;

            return self::ORDER_STATE_OK;
        } else {
            return parent::_sendOrderByEmail($oUser, $oBasket, $oUserPayment);
        }
    }

    public function sendOrderByEmailForPayPalPUI()
    {
        // add user, basket and payment to order
        $this->_oUser =  $this->getOrderUser();
        $this->_oBasket = $this->_getOrderBasket(false);
        $this->_addOrderArticlesToBasket($this->_oBasket, $this->getOrderArticles(true));
        $this->_oBasket->calculateBasket(true);
        $this->_oPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $this->_oPayment->load($this->_oBasket->getPaymentId());

        $oxEmail = oxNew(\OxidEsales\Eshop\Core\Email::class);

        // send order email to user
        $oxEmail->sendOrderEMailToUser($this);

        // send order email to shop owner
        $oxEmail->sendOrderEMailToOwner($this);
    }

    public function getPayPalBankData()
    {
        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payment->load($this->oxorder__oxpaymenttype->value);

        if ($payment->oxpayments__agpaypalpaymentmethod->value === PaymentSource::PAY_UPON_INVOICE) {
            if (class_exists('\OxidEsales\EshopCommunity\Internal\Container\ContainerFactory')) {
                $container = ContainerFactory::getInstance()->getContainer();
                $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);
                $queryBuilder = $queryBuilderFactory->create();

                $data = $queryBuilder->select('oxid')
                    ->from('agpaypalbankdata')
                    ->where('agpaypalbankdata.oxorderid = :orderId')
                    ->setParameter('orderId', $this->getId())
                    ->execute();

                $bankDataId = $data->fetchColumn();
            } else {
                $rs = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->select('SELECT oxid FROM agpaypalbankdata WHERE agpaypalbankdata.oxorderid = :orderId', ['orderId' => $this->getId()]);
                $bankDataId = current($rs->getFields());
            }

            if ($bankDataId) {
                $bankData = oxNew(\Aggrosoft\PayPal\Application\Model\PayPalBankData::class);
                $bankData->load($bankDataId);
                return $bankData;
            }
        }
    }

    public function finalizeOrder(\OxidEsales\Eshop\Application\Model\Basket $oBasket, $oUser, $blRecalculatingOrder = false)
    {
        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payment->load($oBasket->getPaymentId());

        if ($payment && $payment->oxpayments__agpaypalpaymentmethod->value) {
            $this->_blSkipOrderMails = true;
            $this->setValidateDeliveryAddressMD5(false);
            \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->startTransaction();
        }

        $iRet = parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);

        if (!$blRecalculatingOrder && $payment && $payment->oxpayments__agpaypalpaymentmethod->value && ($iRet === self::ORDER_STATE_OK || $iRet === self::ORDER_STATE_MAILINGERROR)) {
            try {
                $iPayPalReturn = $this->finalizePayPalOrder($oBasket, $oUser);
            } catch (\Exception $ex) {
                $iPayPalReturn = self::ORDER_STATE_PAYMENTERROR;
            }

            if ($iPayPalReturn === self::ORDER_STATE_PAYMENTERROR) {
                $iRet = $iPayPalReturn;
                \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->rollbackTransaction();
            }else{
                \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->commitTransaction();
                if ($payment->oxpayments__agpaypalpaymentmethod->value !== PaymentSource::PAY_UPON_INVOICE) {
                    $this->_blSkipOrderMails = false;
                    $iRet = $this->_sendOrderByEmail($oUser, $oBasket, $this->getPaymentType());
                }
            }
        }

        return $iRet;
    }

    protected function finalizePayPalOrder(\OxidEsales\Eshop\Application\Model\Basket $oBasket, $oUser)
    {
        $oBasket->setSkipVouchersChecking(true);
        $oBasket->setStockCheckMode(false);

        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payment->load($this->oxorder__oxpaymenttype->value);

        if ($payment->oxpayments__agpaypalpaymentmethod->value) {

            // Store if was express for later analytics
            $this->oxorder__agpaypalisexpress = new \OxidEsales\Eshop\Core\Field(Registry::getSession()->getVariable('ppexpresscomplete'));

            if ($payment->oxpayments__agpaypalpaymentmethod->value === PaymentSource::PAY_UPON_INVOICE) {
                $paypal = new PayPalInitiator(Registry::getConfig()->getCurrentShopUrl() . 'index.php?cl=order&fnc=execute');
                $paypal->setBasket($oBasket);
                $paypal->setRedirect(false);
                $paypal->setOrderNumber($this->oxorder__oxordernr->value);

                try {
                    $paypal->initiate();
                } catch (RestException $re) {
                    Registry::getSession()->setVariable('ppexpresscomplete', 0);
                    Registry::getSession()->setVariable('pptoken', '');
                    \OxidEsales\Eshop\Core\Registry::getUtilsView()->addErrorToDisplay($re);
                    return self::ORDER_STATE_PAYMENTERROR;
                } catch (\Exception $e) {
                    Registry::getSession()->setVariable('ppexpresscomplete', 0);
                    Registry::getSession()->setVariable('pptoken', '');
                    \OxidEsales\Eshop\Core\Registry::getUtilsView()->addErrorToDisplay('ERR_PAYPAL_ORDER_CREATE_FAILED');
                    return self::ORDER_STATE_PAYMENTERROR;
                }
            }

            $token = Registry::getSession()->getVariable('pptoken');

            if ($token) {
                $client = new PayPalRestClient();

                if (!PaymentSource::isPUI($payment->oxpayments__agpaypalpaymentmethod->value)) {
                    // Send order number to PayPal
                    $request = new UpdateOrderDetailsRequest($token, $this->oxorder__oxordernr->value);

                    try {
                        $client->execute($request);
                    } catch (\Exception $e) {
                        Registry::getSession()->setVariable('ppexpresscomplete', 0);
                        Registry::getSession()->setVariable('pptoken', '');
                        \OxidEsales\Eshop\Core\Registry::getUtilsView()->addErrorToDisplay('ERR_PAYPAL_ORDER_UPDATE_FAILED');
                        return self::ORDER_STATE_PAYMENTERROR;
                    }

                    // Capture order
                    $request = CapturePaymentRequestFactory::create($token);

                    try {
                        $response = $client->execute($request);
                    } catch (\Exception $e) {
                        Registry::getSession()->setVariable('ppexpresscomplete', 0);
                        Registry::getSession()->setVariable('pptoken', '');
                        \OxidEsales\Eshop\Core\Registry::getUtilsView()->addErrorToDisplay('ERR_PAYPAL_CAPTURE_FAILED');
                        return self::ORDER_STATE_PAYMENTERROR;
                    }

                    $capture = $response->purchase_units[0]->payments->captures[0];

                    if (!$capture || $capture->status === 'DENIED') {
                        Registry::getSession()->setVariable('ppexpresscomplete', 0);
                        Registry::getSession()->setVariable('pptoken', '');
                        \OxidEsales\Eshop\Core\Registry::getUtilsView()->addErrorToDisplay('ERR_PAYPAL_CAPTURE_DENIED');
                        return self::ORDER_STATE_PAYMENTERROR;
                    } elseif ($capture->status === 'COMPLETED') {
                        $this->oxorder__oxpaid = new \OxidEsales\Eshop\Core\Field(date("Y-m-d H:i:s"));
                    }

                    $this->oxorder__agpaypalcaptureid = new \OxidEsales\Eshop\Core\Field($capture->id);
                    $this->oxorder__agpaypaltransstatus = new \OxidEsales\Eshop\Core\Field($capture->status);
                }

                $this->oxorder__oxtransid = new \OxidEsales\Eshop\Core\Field($token);
                $this->save();
            } else {
                Registry::getSession()->setVariable('ppexpresscomplete', 0);
                Registry::getSession()->setVariable('pptoken', '');
                \OxidEsales\Eshop\Core\Registry::getUtilsView()->addErrorToDisplay('ERR_PAYPAL_TOKEN_MISSING');
                return self::ORDER_STATE_PAYMENTERROR;
            }
        }
    }

    public function validateDeliveryAddress($oUser)
    {
        $state = parent::validateDeliveryAddress($oUser);

        if ($state === self::ORDER_STATE_INVALIDDELADDRESSCHANGED && !$this->getValidateDeliveryAddressMD5()) {
            return 0;
        } else {
            return $state;
        }
    }

    /**
     * @return bool
     */
    public function getValidateDeliveryAddressMD5(): bool
    {
        return $this->_blValidateDeliveryAddressMD5;
    }

    /**
     * @param bool $blValidateDeliveryAddressMD5
     */
    public function setValidateDeliveryAddressMD5(bool $blValidateDeliveryAddressMD5): void
    {
        $this->_blValidateDeliveryAddressMD5 = $blValidateDeliveryAddressMD5;
    }


}
