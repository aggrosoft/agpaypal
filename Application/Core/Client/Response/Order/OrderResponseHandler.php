<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Response\Order;

class OrderResponseHandler
{
    public static function handle ($response, $user, $returnToken) {

        //store response id
        $savedBasket = $user->getBasket('paypalbasket');
        $savedBasket->oxuserbaskets__agpaypaltoken = new \OxidEsales\Eshop\Core\Field($response->id);
        $savedBasket->oxuserbaskets__agpaypalreturntoken = new \OxidEsales\Eshop\Core\Field($returnToken);
        $savedBasket->oxuserbaskets__oxpublic = new \OxidEsales\Eshop\Core\Field(0);
        $savedBasket->save();

        $links = $response->links;
        $approve = current(array_filter($links, function ($link) {
            return $link->rel == 'approve' || $link->rel == 'payer-action';
        }));
        return $approve->href;
    }
}