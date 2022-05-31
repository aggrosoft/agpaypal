<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Exception;

class RestException extends \Exception
{
    protected $details;

    public function __construct($message = "", $code = 0, $previous = null, $details = [])
    {
        parent::__construct($message, $code, $previous);
        $this->details = $details;
    }

    public function getDetails ()
    {
        return $this->details;
    }
}