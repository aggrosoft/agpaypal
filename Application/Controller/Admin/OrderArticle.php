<?php

namespace Aggrosoft\PayPal\Application\Controller\Admin;

class OrderArticle extends OrderArticle_parent
{
    public function storno()
    {
        parent::storno();

        $myConfig = $this->getConfig();

        $sOrderArtId = \OxidEsales\Eshop\Core\Registry::getConfig()->getRequestParameter('sArtID');
        $oArticle = oxNew(\OxidEsales\Eshop\Application\Model\OrderArticle::class);
        $oArticle->load($sOrderArtId);

        $oArticle->cancelPayPalOrderArticle();
    }
}
