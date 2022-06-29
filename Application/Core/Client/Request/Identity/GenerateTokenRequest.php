<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Identity;

use Aggrosoft\PayPal\Application\Core\Client\Request\IPayPalRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class GenerateTokenRequest extends RequestObject implements IPayPalRequest
{
    public function getBody()
    {
        return '';
    }

    public function getEndpoint()
    {
        return 'v1/identity/generate-token';
    }

    public function getHeaders()
    {
        return [];
    }

    public function getMethod()
    {
        return 'POST';
    }
}
