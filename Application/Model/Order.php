<?php

namespace Aggrosoft\PayPal\Application\Model;

use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class Order extends Order_parent
{
    public function setOrderNumber ()
    {
        $this->_setNumber();
    }

    protected function _sendOrderByEmail($oUser = null, $oBasket = null, $oPayment = null)
    {
        if ($oPayment && $oPayment->oxpayments__agpaypalpaymentmethod->value === PaymentSource::PAY_UPON_INVOICE) {
            // Mail will be sent in capture webhook as we need bank data and capturing state
            $this->_oUser = $oUser;
            $this->_oBasket = $oBasket;
            $this->_oPayment = $oPayment;

            return self::ORDER_STATE_OK;
        } else {
            return parent::_sendOrderByEmail($oUser, $oBasket, $oPayment);
        }
    }

    public function sendOrderByEmailForPayPalPUI ()
    {
        // add user, basket and payment to order
        $this->_oUser =  $this->getOrderUser();
        $this->_oBasket = $this->_getOrderBasket(false);
        $this->_oPayment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $this->_oPayment->load($this->_oBasket->getPaymentId());

        $oxEmail = oxNew(\OxidEsales\Eshop\Core\Email::class);

        // send order email to user
        $oxEmail->sendOrderEMailToUser($this);

        // send order email to shop owner
        $oxEmail->sendOrderEMailToOwner($this);
    }

    public function getPayPalBankData ()
    {
        $payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
        $payment->load($this->oxorder__oxpaymenttype->value);

        if ($payment->oxpayments__agpaypalpaymentmethod->value === PaymentSource::PAY_UPON_INVOICE) {

            $container = ContainerFactory::getInstance()->getContainer();
            $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);
            $queryBuilder = $queryBuilderFactory->create();

            $data = $queryBuilder->select('oxid')
                ->from('agpaypalbankdata')
                ->where('agpaypalbankdata.oxorderid = :orderId')
                ->setParameter('orderId', $this->getId())
                ->execute();

            $bankDataId = $data->fetchColumn();

            if ($bankDataId) {
                $bankData = oxNew(\Aggrosoft\PayPal\Application\Model\PayPalBankData::class);
                $bankData->load($bankDataId);
                return $bankData;
            }
        }
    }

}