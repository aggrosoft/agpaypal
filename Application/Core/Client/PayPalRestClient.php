<?php

namespace Aggrosoft\PayPal\Application\Core\Client;

use Aggrosoft\PayPal\Application\Core\Client\Exception\RestException;
use Aggrosoft\PayPal\Application\Core\Client\Request\IPayPalRequest;
use Aggrosoft\PayPal\Application\Core\Client\Exception\AuthenticationException;
use OxidEsales\Eshop\Core\Registry;

class PayPalRestClient
{
    public const LIVE_API_URL = 'https://api-m.paypal.com/';
    public const SANDBOX_API_URL = 'https://api-m.sandbox.paypal.com/';

    public const LIVE_TOKEN_URL = 'https://api-m.paypal.com/v1/oauth2/token';
    public const SANDBOX_TOKEN_URL = 'https://api-m.sandbox.paypal.com/v1/oauth2/token';

    /**
     * @var string
     */
    private $clientId;
    /**
     * @var string
     */
    private $clientSecret;
    /**
     * @var string
     */
    private $mailAddress;
    /**
     * @var bool
     */
    private $sandbox;
    /**
     * @var string
     */
    private $token;
    /**
     * @var string
     */
    private $logLevel;
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    public function __construct()
    {
        $config = Registry::getConfig();
        $this->clientId = $config->getConfigParam('sPayPalClientId', null, 'module:agpaypal');
        $this->clientSecret = $config->getConfigParam('sPayPalClientSecret', null, 'module:agpaypal');
        $this->mailAddress = $config->getConfigParam('sPayPalEmailAddress', null, 'module:agpaypal');
        $this->sandbox = (bool) $config->getConfigParam('blPayPalSandboxMode', null, 'module:agpaypal');
        $this->logLevel = $config->getConfigParam('sPayPalLogLevel', null, 'module:agpaypal');
        $this->client = new \GuzzleHttp\Client();
    }

    public function execute(IPayPalRequest $request)
    {
        $response = $this->client->request($request->getMethod(), $this->getApiUrl().$request->getEndpoint(), [
            'headers' => array_merge([
                'Authorization' => 'Bearer ' . $this->getToken(),
                'Content-Type' => 'application/json',
                'PayPal-Request-Id' => \OxidEsales\Eshop\Core\Registry::getUtilsObject()->generateUID(),
                'PayPal-Partner-Attribution-Id' => 'Oxid_Cart_Aggrosoft_PPCP'
            ], $request->getHeaders()),
            'http_errors' => false,
            'body' => $request->getBody(),
        ]);

        $result = json_decode($response->getBody()->getContents());
        $this->log($request, $result, $response->getStatusCode());

        if ($response->getStatusCode() > 299) {
            throw new RestException('PAYPAL_ERROR_'.strtoupper(str_replace(' ', '_', $result->details[0]->issue)), $response->getStatusCode(), null, ['request' => $request->getBody(), 'response' => $result]);
        }

        return $result;
    }

    public function exchangeAuthCode ($authCode, $nonce, $sharedId, $sellerId) {
        $request = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic '.base64_encode($sharedId.':')
            ],
            'body' => 'grant_type=authorization_code&code=' . $authCode . '&code_verifier=' . $nonce,
            'http_errors' => false
        ];

        $response = $this->client->request('POST',$this->getTokenUrl(), $request);
        $result = json_decode($response->getBody()->getContents());
        $this->log($request, $result, $response->getStatusCode());

        if ($result->error) {
            throw new AuthenticationException($result->error_description);
        }

        $request = [
            'headers' => [
                'Authorization' => 'Bearer ' . $result->access_token,
                'Content-Type' => 'application/json',
                'PayPal-Request-Id' => \OxidEsales\Eshop\Core\Registry::getUtilsObject()->generateUID(),
                'PayPal-Partner-Attribution-Id' => 'Oxid_Cart_Aggrosoft_PPCP'
            ],
            'http_errors' => false
        ];

        $response = $this->client->request('GET',$this->getApiUrl().'v1/customer/partners/'.$sellerId.'/merchant-integrations', $request);
        $result = json_decode($response->getBody()->getContents());
        $this->log($request, $result, $response->getStatusCode());

        if ($result->error) {
            throw new AuthenticationException($result->error_description);
        }

        return $result;

    }

    public function invalidateToken()
    {
        $cacheKey = 'pptoken_'.intval($this->sandbox).'_'.\OxidEsales\Eshop\Core\Registry::getConfig()->getShopId();
        $utils = \OxidEsales\Eshop\Core\Registry::getUtils();
        $utils->toFileCache($cacheKey, '', 0);
        $this->token = null;
    }

    private function getToken()
    {
        if (!$this->token) {
            $cacheKey = 'pptoken_'.intval($this->sandbox).'_'.\OxidEsales\Eshop\Core\Registry::getConfig()->getShopId();
            $utils = \OxidEsales\Eshop\Core\Registry::getUtils();
            $cachedToken = $utils->fromFileCache($cacheKey);

            if ($cachedToken) {
                $this->token = $cachedToken;
            } else {
                $request = [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                    'auth' => [
                        $this->clientId,
                        $this->clientSecret,
                    ],
                    'form_params' => [
                        'grant_type' => 'client_credentials'
                    ],
                    'http_errors' => false
                ];

                $response = $this->client->request('POST', $this->getTokenUrl(), $request);
                $result = json_decode($response->getBody()->getContents());

                $this->log($request, $result, $response->getStatusCode());

                if ($result->error) {
                    throw new AuthenticationException($result->error_description);
                } else {
                    $this->token = $result->access_token;
                    $utils->toFileCache($cacheKey, $this->token, $result->expires_in - 60);
                }
            }
        }
        return $this->token;
    }

    private function getTokenUrl()
    {
        return $this->sandbox ? self::SANDBOX_TOKEN_URL : self::LIVE_TOKEN_URL;
    }

    private function getApiUrl()
    {
        return $this->sandbox ? self::SANDBOX_API_URL : self::LIVE_API_URL;
    }

    private function log($request, $result, $code)
    {
        if ($this->logLevel === 'all' || $this->logLevel === 'error' && $code > 299) {
            $log = "#$code [" . date("d/M/Y H:i:s") . "]\n";
            if ($request instanceof IPayPalRequest) {
                $log .= "Request: \n\n" . $request->getMethod().': '. $this->getApiUrl().$request->getEndpoint() . "\n\n" . json_encode($request, JSON_PRETTY_PRINT) . "\n\n";
            } else {
                $log .= "Request: \n\n" . json_encode($request, JSON_PRETTY_PRINT) . "\n\n";
            }
            $log .= "Response: \n\n" . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
            $log .= "########################################################################\n\n";

            file_put_contents(getShopBasePath().'/log/paypal.log', $log, FILE_APPEND);
        }
    }

    public function logExternal($message) {
        if ($this->logLevel === 'all'){
            $log = "#EXTERNAL [" . date("d/M/Y H:i:s") . "]\n";
            $log .= "Message: \n\n" . $message . "\n\n";
            $log .= "########################################################################\n\n";
            file_put_contents(getShopBasePath().'/log/paypal.log', $log, FILE_APPEND);
        }
    }
}
