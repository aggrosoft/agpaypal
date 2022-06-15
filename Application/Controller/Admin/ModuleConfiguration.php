<?php

namespace Aggrosoft\PayPal\Application\Controller\Admin;

use Aggrosoft\PayPal\Application\Core\Client\Exception\AuthenticationException;
use Aggrosoft\PayPal\Application\Core\Client\Exception\RestException;
use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Webhook\WebhookInitiator;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleSettingBridgeInterface;
use OxidEsales\Eshop\Core\Registry;

class ModuleConfiguration extends ModuleConfiguration_parent
{
    public function saveConfVars()
    {

        parent::saveConfVars();

        $moduleId = $this->_sEditObjectId
            ?? Registry::getRequest()->getRequestEscapedParameter('oxid')
            ?? Registry::getSession()->getVariable('saved_oxid');

        if ($moduleId === 'agpaypal') {

            $config = Registry::getConfig();
            $initiator = new WebhookInitiator();
            $webhookId = '';

            $client = new PayPalRestClient();
            $client->invalidateToken();

            try {
                if ($config->getConfigParam('sPayPalClientId', null, 'module:agpaypal') && $config->getConfigParam('sPayPalClientId', null, 'module:agpaypal')) {
                    $webhookId = $initiator->initiate($config->getConfigParam('sPayPalWebhookId', null, 'module:agpaypal'));
                }
            }catch(AuthenticationException $e) {
                \OxidEsales\Eshop\Core\Registry::getUtilsView()->addErrorToDisplay('ERR_PAYPAL_AUTHENTICATION_FAILED');
            }catch(RestException $e) {
                \OxidEsales\Eshop\Core\Registry::getUtilsView()->addErrorToDisplay($e);
            }

            if(class_exists('\OxidEsales\EshopCommunity\Internal\Container\ContainerFactory')){
                $moduleSettingBridge = ContainerFactory::getInstance()
                    ->getContainer()
                    ->get(ModuleSettingBridgeInterface::class);

                $moduleSettingBridge->save('sPayPalWebhookId', $webhookId, 'agpaypal');
            } else {
                $config->saveShopConfVar('str', 'sPayPalWebhookId', $webhookId, null, 'module:agpaypal');
            }


        }
    }
}