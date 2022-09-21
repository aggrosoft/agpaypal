<?php

namespace Aggrosoft\PayPal\Application\Model;

use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class Address extends Address_parent
{
    public function getExistingAddressId()
    {
        if (class_exists('\OxidEsales\EshopCommunity\Internal\Container\ContainerFactory')) {
            $container = ContainerFactory::getInstance()->getContainer();
            $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);
            $queryBuilder = $queryBuilderFactory->create();

            $data = $queryBuilder->select('oxid')
                ->from('oxaddress')
                ->where('oxaddress.agpaypalhash = :hash')
                ->setParameter('hash', $this->getPayPalAddressHash())
                ->execute();

            return $data->fetchColumn;
        } else {
            $rs = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->select('SELECT oxid FROM oxaddress WHERE oxaddress.agpaypalhash = :hash', ['hash' => $this->getPayPalAddressHash()]);
            return current($rs->getFields());
        }
    }

    public function getPayPalAddressHash()
    {
        return md5(
            $this->oxaddress__oxfname->value .
            $this->oxaddress__oxlname->value .
            $this->oxaddress__oxstreet->value .
            $this->oxaddress__oxstreetnr->value .
            $this->oxaddress__oxaddinfo->value .
            $this->oxaddress__oxzip->value .
            $this->oxaddress__oxcity->value .
            $this->oxaddress__oxcountryid->value .
            $this->oxaddress__oxuserid->value .
            $this->oxaddress__oxstateid->value
        );
    }
}
