<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request;

interface IPayPalRequest
{
    public function getBody();
    public function getEndpoint();
    public function getHeaders();
    public function getMethod();
}