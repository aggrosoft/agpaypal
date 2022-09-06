<?php

namespace Aggrosoft\PayPal\Application\Core\Webhook;

use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Request\Webhooks\CreateWebhookRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\Webhooks\DeleteWebhookRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\Webhooks\ListWebhooksRequest;

class WebhookInitiator
{
    private const NEEDED_EVENTS = [
        [
            'name' => 'PAYMENT.CAPTURE.COMPLETED'
        ],
        [
            'name' => 'PAYMENT.CAPTURE.DENIED'
        ],
        [
            'name' => 'CHECKOUT.PAYMENT-APPROVAL.REVERSED'
        ],
        [
            'name' => 'PAYMENT.CAPTURE.REVERSED'
        ],
        [
            'name' => 'PAYMENT.CAPTURE.REFUNDED'
        ],
        [
            'name' => 'CHECKOUT.ORDER.APPROVED'
        ]
    ];

    public function initiate($webhookId = null)
    {
        $client = new PayPalRestClient();
        $request = new ListWebhooksRequest();
        $response = $client->execute($request);
        $webhooks = $response->webhooks;

        $url = $this->getWebhookUrl();

        // Check if there is already a good webhook
        foreach ($webhooks as $webhook) {
            if ($webhook->url === $url) {
                if ($this->webhookContainsAllNeededEvents($webhook)) {
                    return $webhook->id;
                } else {
                    // delete webhook and recreate later
                    $request = new DeleteWebhookRequest($webhook->id);
                    $client->execute($request);
                }
            }
        }

        // Create new webhook
        $request = new CreateWebhookRequest();
        $request->setUrl($url);
        $request->setEventTypes(self::NEEDED_EVENTS);
        $response = $client->execute($request);
        return $response->id;
    }

    public function getWebhookUrl(): string
    {
        $url = \OxidEsales\Eshop\Core\Registry::getConfig()->getSslShopUrl() ?: \OxidEsales\Eshop\Core\Registry::getConfig()->getShopUrl();
        return $url . 'index.php?cl=aggrosoft_paypal_webhook';
    }

    private function webhookContainsAllNeededEvents($webhook): bool
    {
        $events = array_map(function ($event) {
            return $event->name;
        }, $webhook->event_types);
        $neededEvents = array_map(function ($event) {
            return $event['name'];
        }, self::NEEDED_EVENTS);

        return count(array_diff($neededEvents, $events)) === 0;
    }
}
