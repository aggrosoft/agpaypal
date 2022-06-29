<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Webhooks;

use Aggrosoft\PayPal\Application\Core\Client\Request\IPayPalRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class ListWebhooksRequest extends RequestObject implements IPayPalRequest
{
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
        return 'GET';
    }

    public function getBody()
    {
        return '';
    }
}
