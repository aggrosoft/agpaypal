<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Webhooks;

use Aggrosoft\PayPal\Application\Core\Client\Request\IPayPalRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class DeleteWebhookRequest extends RequestObject implements IPayPalRequest
{
    /**
     * @var string
     */
    protected $webhookId;

    public function __construct($webhookId)
    {
        $this->webhookId = $webhookId;
    }

    public function getEndpoint()
    {
        return 'v1/notifications/webhooks/'.$this->webhookId;
    }

    public function getHeaders()
    {
        return [];
    }

    public function getMethod()
    {
        return 'DELETE';
    }

    public function getBody()
    {
        return '';
    }
}
