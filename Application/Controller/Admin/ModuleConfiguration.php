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
            $this->updateWebhook();
        }
    }

    public function paypalonboarding()
    {
        $authCode = Registry::getRequest()->getRequestEscapedParameter('ppauthcode');
        $nonce = Registry::getRequest()->getRequestEscapedParameter('ppnonce');
        $sharedId = Registry::getRequest()->getRequestEscapedParameter('ppsharedid');

        if ($authCode && $nonce) {
            $client = new PayPalRestClient();

            try {
                $credentials = $client->exchangeAuthCode($authCode, $nonce, $sharedId, $this->getPayPalPartnerId());
            } catch (AuthenticationException $e) {
                \OxidEsales\Eshop\Core\Registry::getUtilsView()->addErrorToDisplay('ERR_PAYPAL_AUTHENTICATION_FAILED');
            }

            if ($credentials) {
                $config = Registry::getConfig();
                if (class_exists('\OxidEsales\EshopCommunity\Internal\Container\ContainerFactory')) {
                    $moduleSettingBridge = ContainerFactory::getInstance()
                        ->getContainer()
                        ->get(ModuleSettingBridgeInterface::class);

                    $moduleSettingBridge->save('sPayPalClientId', $credentials->client_id, 'agpaypal');
                    $moduleSettingBridge->save('sPayPalClientSecret', $credentials->client_secret, 'agpaypal');
                } else {
                    $config->saveShopConfVar('str', 'sPayPalClientId',  $credentials->client_id, null, 'module:agpaypal');
                    $config->saveShopConfVar('str', 'sPayPalClientSecret',  $credentials->client_secret, null, 'module:agpaypal');
                }
            }

        }
    }

    public function getPayPalPartnerClientId()
    {
        if ($this->isPayPalSandbox()){
            return 'AaTsKBVHPEo1hBSH0gQlz-5mtQ-bHIYLu1DeDXnSQ4lQF2yEQY4mzvwQuQXuvKR61zUB0jv7FEdhFmd1';
        }else{
            return 'AZYk93O-O5el3VkmM1T20qvu0KaqiZTJHV_Y34xn6AQObL9B7CE3LS72zlI_HyKamf5FQebuK8L41Big';
        }

    }

    public function getPayPalPartnerId()
    {
        if ($this->isPayPalSandbox()){
            return 'T9AVHNZL5M8QJ';
        }else{
            return 'NZ8NAQYGGFZ84';
        }

    }

    public function getPayPalPartnerLogoUrl()
    {
        return 'https://www.aggrosoft.de/uploads/cms/c4a21d6e-aeb3-11ec-b3ac-7054d2aad2bf-7a5ee69aa5408e92004eb619cfc0ede8.jpg';
    }

    public function getPayPalSellerNonce()
    {
        return hash('sha512', $this->getPayPalRandomSeed());
    }

    public function isPayPalSandbox()
    {
        return Registry::getConfig()->getConfigParam('blPayPalSandboxMode', null, 'module:agpaypal');
    }

    private function updateWebhook()
    {
        $config = Registry::getConfig();
        $initiator = new WebhookInitiator();
        $webhookId = '';

        $client = new PayPalRestClient();
        $client->invalidateToken();

        try {
            if ($config->getConfigParam('sPayPalClientId', null, 'module:agpaypal') && $config->getConfigParam('sPayPalClientSecret', null, 'module:agpaypal')) {
                $webhookId = $initiator->initiate();
            }
        } catch (AuthenticationException $e) {
            \OxidEsales\Eshop\Core\Registry::getUtilsView()->addErrorToDisplay('ERR_PAYPAL_AUTHENTICATION_FAILED');
        } catch (RestException $e) {
            \OxidEsales\Eshop\Core\Registry::getUtilsView()->addErrorToDisplay($e);
        }

        if (class_exists('\OxidEsales\EshopCommunity\Internal\Container\ContainerFactory')) {
            $moduleSettingBridge = ContainerFactory::getInstance()
                ->getContainer()
                ->get(ModuleSettingBridgeInterface::class);

            $moduleSettingBridge->save('sPayPalWebhookId', $webhookId, 'agpaypal');
        } else {
            $config->saveShopConfVar('str', 'sPayPalWebhookId', $webhookId, null, 'module:agpaypal');
        }
    }

    private function getPayPalRandomSeed($bits = 256)
    {
        $bytes = ceil($bits / 8);
        $return = '';
        for ($i = 0; $i < $bytes; $i++) {
            $return .= chr(mt_rand(0, 255));
        }
        return $return;
    }
}
