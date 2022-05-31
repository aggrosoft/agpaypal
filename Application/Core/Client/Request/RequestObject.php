<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request;

class RequestObject implements \JsonSerializable {
    public function jsonSerialize(): array
    {
        return array_filter((array) $this, function ($var) {
            return !is_null($var);
        });
    }
}