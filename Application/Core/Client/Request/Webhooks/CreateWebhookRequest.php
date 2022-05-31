<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Webhooks;

use Aggrosoft\PayPal\Application\Core\Client\Request\IPayPalRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\JSONBodyTrait;
use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class CreateWebhookRequest extends RequestObject implements IPayPalRequest
{

    use JSONBodyTrait;

    /**
     * @var string
     */
    public $url;

    /**
     * @var array
     */
    public $event_types;

    public function getEndpoint()
    {
        return 'v1/notifications/webhooks';
    }

    public function getHeaders()
    {
        return [];
    }

    public function getMethod()
    {
        return 'POST';
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function getEventTypes(): array
    {
        return $this->event_types;
    }

    /**
     * @param array $event_types
     */
    public function setEventTypes(array $event_types): void
    {
        $this->event_types = $event_types;
    }

}