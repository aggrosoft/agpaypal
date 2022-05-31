<?php

namespace Aggrosoft\PayPal\Application\Core\Client\Request\Webhooks;

use Aggrosoft\PayPal\Application\Core\Client\Request\IPayPalRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\JSONBodyTrait;
use Aggrosoft\PayPal\Application\Core\Client\Request\RequestObject;

class VerifyWebhookSignatureRequest extends RequestObject implements IPayPalRequest
{
    use JSONBodyTrait;

    /**
     * @var string
     */
    public $auth_algo;

    /**
     * @var string
     */
    public $cert_url;

    /**
     * @var string
     */
    public $transmission_id;

    /**
     * @var string
     */
    public $transmission_sig;

    /**
     * @var string
     */
    public $transmission_time;

    /**
     * @var string
     */
    public $webhook_id;

    /**
     * @var object
     */
    public $webhook_event;

    public function getEndpoint()
    {
        return 'v1/notifications/verify-webhook-signature';
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
    public function getAuthAlgo(): string
    {
        return $this->auth_algo;
    }

    /**
     * @param string $auth_algo
     */
    public function setAuthAlgo(string $auth_algo): void
    {
        $this->auth_algo = $auth_algo;
    }

    /**
     * @return string
     */
    public function getCertUrl(): string
    {
        return $this->cert_url;
    }

    /**
     * @param string $cert_url
     */
    public function setCertUrl(string $cert_url): void
    {
        $this->cert_url = $cert_url;
    }

    /**
     * @return string
     */
    public function getTransmissionId(): string
    {
        return $this->transmission_id;
    }

    /**
     * @param string $transmission_id
     */
    public function setTransmissionId(string $transmission_id): void
    {
        $this->transmission_id = $transmission_id;
    }

    /**
     * @return string
     */
    public function getTransmissionSig(): string
    {
        return $this->transmission_sig;
    }

    /**
     * @param string $transmission_sig
     */
    public function setTransmissionSig(string $transmission_sig): void
    {
        $this->transmission_sig = $transmission_sig;
    }

    /**
     * @return string
     */
    public function getTransmissionTime(): string
    {
        return $this->transmission_time;
    }

    /**
     * @param string $transmission_time
     */
    public function setTransmissionTime(string $transmission_time): void
    {
        $this->transmission_time = $transmission_time;
    }

    /**
     * @return string
     */
    public function getWebhookId(): string
    {
        return $this->webhook_id;
    }

    /**
     * @param string $webhook_id
     */
    public function setWebhookId(string $webhook_id): void
    {
        $this->webhook_id = $webhook_id;
    }

    /**
     * @return object
     */
    public function getWebhookEvent(): object
    {
        return $this->webhook_event;
    }

    /**
     * @param array $webhook_event
     */
    public function setWebhookEvent(object $webhook_event): void
    {
        $this->webhook_event = $webhook_event;
    }

}