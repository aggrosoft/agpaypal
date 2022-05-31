<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request;

trait JSONBodyTrait
{
    public function getBody () {
        return json_encode($this);
    }
}