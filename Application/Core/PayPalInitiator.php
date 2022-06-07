<?php

namespace Aggrosoft\PayPal\Application\Core;

use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\ApplicationContext;
use Aggrosoft\PayPal\Application\Core\Client\Response\Order\OrderResponseHandler;
use Aggrosoft\PayPal\Application\Core\Factory\Request\Order\CreateOrderRequestFactory;
use OxidEsales\Eshop\Core\Registry;

class PayPalInitiator
{

    protected $returnUrl = '';
    protected $redirect = true;
    protected $shippingPreference = ApplicationContext::SHIPPING_PREFERENCE_SET_PROVIDED_ADDRESS;
    protected $userAction = ApplicationContext::USER_ACTION_CONTINUE;
    protected $products;

    public function __construct ($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * Create PayPal order and redirect
     */
    public function initiate ()
    {
        $returnToken = $this->generateReturnToken();
        $savedBasket = PayPalBasketHandler::savePayPalBasket($returnToken, $this->products);
        $basket = PayPalBasketHandler::restoreBasketFromUserBasket($savedBasket, Registry::getConfig()->getUser());
        $user = $basket->getBasketUser();
        $payment = $this->getPayment();

        $request = CreateOrderRequestFactory::create($user, $basket, $payment, $this->returnUrl . '&pptoken='.$returnToken, $this->shippingPreference, $this->userAction);
        $client = $this->getPayPalClient();

        $response = $client->execute($request);

        $redirectUrl = OrderResponseHandler::handle($response, $savedBasket);

        if ($redirectUrl && $this->redirect) {
            Registry::getUtils()->redirect($redirectUrl, false, 303);
        } else {
            Registry::getSession()->setVariable('pptoken', $response->id);
        }

        return [
            'orderId' => $response->id,
            'returnToken' => $returnToken
        ];
    }

    protected function getPayPalClient ()
    {
        if (!$this->paypalClient) {
            $this->paypalClient = new PayPalRestClient();
        }
        return $this->paypalClient;
    }

    protected function getPayment ()
    {
        if(!$this->payment) {
            $session = Registry::getSession();
            if (!($paymentId = Registry::getRequest()->getRequestEscapedParameter('paymentid'))) {
                $paymentId = $session->getVariable('paymentid');
            }

            if($paymentId) {
                $this->payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
                $this->payment->load($paymentId);
            }
        }
        return $this->payment;
    }

    protected function generateReturnToken ()
    {
        return bin2hex(random_bytes(64));
    }

    /**
     * @return string
     */
    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    /**
     * @param string $returnUrl
     */
    public function setReturnUrl(string $returnUrl): void
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * @return bool
     */
    public function isRedirect(): bool
    {
        return $this->redirect;
    }

    /**
     * @param bool $redirect
     */
    public function setRedirect(bool $redirect): void
    {
        $this->redirect = $redirect;
    }

    /**
     * @return string
     */
    public function getShippingPreference(): string
    {
        return $this->shippingPreference;
    }

    /**
     * @param string $shippingPreference
     */
    public function setShippingPreference(string $shippingPreference): void
    {
        $this->shippingPreference = $shippingPreference;
    }

    /**
     * @return array
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @param array $products
     */
    public function setProducts(array $products): void
    {
        $this->products = $products;
    }

    /**
     * @return string
     */
    public function getUserAction(): string
    {
        return $this->userAction;
    }

    /**
     * @param string $userAction
     */
    public function setUserAction(string $userAction): void
    {
        $this->userAction = $userAction;
    }



}