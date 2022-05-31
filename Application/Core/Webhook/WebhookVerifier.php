<?php

namespace Aggrosoft\PayPal\Application\Core\Webhook;

use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Request\Webhooks\VerifyWebhookSignatureRequest;

class WebhookVerifier
{

    public const VERIFICATION_STATUS_SUCCESS = 'SUCCESS';
    public const VERIFICATION_STATUS_FAILURE = 'FAILURE';

    public function verifyIncomingWebhook ()
    {
        $headers = apache_request_headers();
        $body = file_get_contents('php://input');

        $input = json_decode($body);

        $request = new VerifyWebhookSignatureRequest();
        $request->setAuthAlgo($headers['Paypal-Auth-Algo']);
        $request->setCertUrl($headers['Paypal-Cert-Url']);
        $request->setTransmissionId($headers['Paypal-Transmission-Id']);
        $request->setTransmissionSig($headers['Paypal-Transmission-Sig']);
        $request->setTransmissionTime($headers['Paypal-Transmission-Time']);
        $request->setWebhookId(\OxidEsales\Eshop\Core\Registry::getConfig()->getConfigParam('sPayPalWebhookId', null, 'module:agpaypal'));
        $request->setWebhookEvent($input);

        $client = new PayPalRestClient();
        $response = $client->execute($request);

        if ($response->verification_status === self::VERIFICATION_STATUS_SUCCESS) {
            return $input;
        }
    }
}