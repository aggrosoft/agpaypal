<?php

namespace Aggrosoft\PayPal\Application\Core;

class Events
{
    public static function onActivate()
    {
        // DB Queries
        $queries = [
            'ALTER TABLE oxpayments ADD COLUMN AGPAYPALPAYMENTMETHOD varchar(30) default NULL',
            'ALTER TABLE oxpayments ADD COLUMN AGPAYPALLANDINGPAGE varchar(30) default NULL',
            'ALTER TABLE oxuserbaskets ADD COLUMN AGPAYPALTOKEN char(32) default NULL',
            'ALTER TABLE oxuserbaskets ADD COLUMN AGPAYPALRETURNTOKEN char(128) default NULL',
            'ALTER TABLE oxuserbaskets ADD COLUMN AGPAYPALPAYMENTID char(32) default NULL',
            'ALTER TABLE oxuserbaskets ADD COLUMN AGPAYPALSHIPPINGID char(32) default NULL',
            'ALTER TABLE oxuserbaskets ADD COLUMN AGPAYPALDELADRID char(32) default NULL',
            'ALTER TABLE oxuserbaskets ADD COLUMN AGPAYPALREMARK text default NULL',
            'ALTER TABLE oxuserbaskets ADD COLUMN AGPAYPALCARDID char(32) default NULL',
            'ALTER TABLE oxuserbaskets ADD COLUMN AGPAYPALCARDTEXT text default NULL',
            'ALTER TABLE oxuserbasketitems ADD COLUMN AGPAYPALWRAPID char(32) default NULL',
            'ALTER TABLE oxorder ADD COLUMN AGPAYPALCAPTUREID char(32) default NULL',
            'ALTER TABLE oxorder ADD COLUMN AGPAYPALTRANSSTATUS char(32) default NULL',
            'ALTER TABLE oxorder ADD COLUMN AGPAYPALREFUNDID char(32) default NULL',
            'ALTER TABLE oxuser ADD COLUMN AGPAYPALPAYERID char(13) default NULL',
            'CREATE TABLE `agpaypalbankdata` (
              `OXID` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
              `OXORDERID` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
              `REFERENCE` varchar(30) NOT NULL,
              `BANKNAME` varchar(255) NOT NULL,
              `BIC` char(11) NOT NULL,
              `IBAN` varchar(34) NOT NULL,
              `ACCOUNTHOLDER` varchar(255) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
            'ALTER TABLE `agpaypalbankdata`
              ADD PRIMARY KEY (`OXID`);',
            'ALTER TABLE oxaddress ADD COLUMN AGPAYPALHASH varchar(32) default NULL',
            'ALTER TABLE oxuserbaskets ADD COLUMN AGPAYPALVOUCHERS text default NULL',
        ];

        foreach ($queries as $query) {
            try {
                \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute($query);
            } catch (\Exception $e) {
            }
        }
    }
}
