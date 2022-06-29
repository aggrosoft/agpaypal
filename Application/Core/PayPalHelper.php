<?php

namespace Aggrosoft\PayPal\Application\Core;

use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\Eshop\Core\Registry;

class PayPalHelper
{
    public static function getFraudNetSessionIdentifier()
    {
        return FraudNet::getSessionIdentifier();
    }

    public static function getFraudNetSourceWebsiteIdentifier()
    {
        return FraudNet::getSourceWebsiteIdentifier();
    }

    public static function isPayPalSandbox()
    {
        return Registry::getConfig()->getConfigParam('blPayPalSandboxMode', null, 'module:agpaypal');
    }

    public static function getPayPalClientId()
    {
        return Registry::getConfig()->getConfigParam('sPayPalClientId', null, 'module:agpaypal');
    }

    public static function showPayPalButtonInDetails()
    {
        return Registry::getConfig()->getConfigParam('blPayPalExpressDetails', null, 'module:agpaypal');
    }

    public static function showPayPalButtonInBasket()
    {
        return Registry::getConfig()->getConfigParam('blPayPalExpressBasket', null, 'module:agpaypal');
    }

    public static function showPayPalMessageInDetails()
    {
        return Registry::getConfig()->getConfigParam('blPayPalMessagesDetails', null, 'module:agpaypal');
    }

    public static function getPayPalPaymentId()
    {
        if (class_exists('\OxidEsales\EshopCommunity\Internal\Container\ContainerFactory')) {
            $container = ContainerFactory::getInstance()->getContainer();
            $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);
            $queryBuilder = $queryBuilderFactory->create();

            $data = $queryBuilder->select('oxid')
                ->from('oxpayments')
                ->where('oxpayments.agpaypalpaymentmethod = :method')
                ->andWhere('oxpayments.oxactive = 1')
                ->setParameter('method', PaymentSource::PAYPAL)
                ->execute();

            return $data->fetchColumn();
        } else {
            $rs = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->select('SELECT oxid FROM oxpayments WHERE oxpayments.agpaypalpaymentmethod = :method AND oxpayments.oxactive = 1', ['method' => PaymentSource::PAYPAL]);
            return current($rs->getFields());
        }
    }
}
