<?php

namespace Aggrosoft\PayPal\Application\Core;

use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use Aggrosoft\PayPal\Application\Core\Factory\Request\Order\CreateOrderRequestFactory;
use OxidEsales\Eshop\Core\Registry;

class ViewConfig extends ViewConfig_parent
{
    public function getFraudNetSessionIdentifier ()
    {
        return FraudNet::getSessionIdentifier();
    }

    public function getFraudNetSourceWebsiteIdentifier ()
    {
        return FraudNet::getSourceWebsiteIdentifier();
    }

    public function isPayPalSandbox ()
    {
        return Registry::getConfig()->getConfigParam('blPayPalSandboxMode', null, 'module:agpaypal');
    }

    public function getPayPalClientId ()
    {
        return Registry::getConfig()->getConfigParam('sPayPalClientId', null, 'module:agpaypal');
    }

    public function getPayPalPaymentId ()
    {
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
    }
}