<?php

namespace Aggrosoft\PayPal\Application\Controller;

use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Request\Identity\GenerateTokenRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\Struct\PaymentSource;
use Aggrosoft\PayPal\Application\Core\PayPalBasketHandler;
use Aggrosoft\PayPal\Application\Core\PayPalInitiator;
use OxidEsales\Eshop\Core\Registry;

class PaymentController extends PaymentController_parent
{
    /**
     * @var \OxidEsales\Eshop\Application\Model\Payment
     */
    protected $payment;

    public function render()
    {
        $result = parent::render();

        // Maybe there is an old token, destroy this
        $token = Registry::getSession()->getVariable('pptoken');
        if ($token) {
            PayPalBasketHandler::destroyUserBasketForToken($token);
            Registry::getSession()->setVariable('pptoken', '');
        }

        // Remove ppexpress flag
        Registry::getSession()->setVariable('ppexpresscomplete', 0);

        $session = Registry::getSession();
        $this->_aViewData['pp_birth_date'] = $session->getVariable('pp_birth_date');
        $this->_aViewData['pp_phone_number'] = $session->getVariable('pp_phone_number');
        $this->_aViewData['pp_phone_country_code'] = $session->getVariable('pp_phone_country_code');

        return $result;
    }

    public function getPayPalCreditCardPaymentMethod()
    {
        $payments = $this->getPaymentList();

        foreach ($payments as $payment) {
            if ($payment->oxpayments__agpaypalpaymentmethod->value === PaymentSource::CARD) {
                return $payment;
            }
        }
    }

    public function getPayPalPayUponInvoicePaymentMethod()
    {
        $payments = $this->getPaymentList();

        foreach ($payments as $payment) {
            if ($payment->oxpayments__agpaypalpaymentmethod->value === PaymentSource::PAY_UPON_INVOICE) {
                return $payment;
            }
        }
    }

    // Used for custom hosted fields
    public function createpaypalorder()
    {
        $session = Registry::getSession();
        $session->setVariable('paymentid', Registry::getRequest()->getRequestEscapedParameter('paymentid'));
        $paypal = new PayPalInitiator(Registry::getConfig()->getCurrentShopUrl() . 'index.php?cl=order&fnc=execute');
        $paypal->setRedirect(false);
        $response = $paypal->initiate();
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    public function validatePayment()
    {
        $result = parent::validatePayment();

        if ($result === 'order') {
            $payment = $this->getPayment();

            if ($payment && $payment->oxpayments__agpaypalpaymentmethod->value) {
                if ($payment->oxpayments__agpaypalpaymentmethod->value === PaymentSource::PAY_UPON_INVOICE) {
                    // Validate and store birthdate / phone number for PUI
                    $birthDate = Registry::getRequest()->getRequestEscapedParameter('pp_birth_date');
                    $phoneCode = Registry::getRequest()->getRequestEscapedParameter('pp_phone_number');
                    $phoneCountryCode = Registry::getRequest()->getRequestEscapedParameter('pp_phone_country_code');

                    $session = Registry::getSession();
                    $session->setVariable('pp_birth_date', $birthDate);
                    $session->setVariable('pp_phone_number', $phoneCode);
                    $session->setVariable('pp_phone_country_code', $phoneCountryCode);

                    if (!$birthDate || !$phoneCode || !$phoneCountryCode) {
                        Registry::getUtilsView()->addErrorToDisplay('PAYPAL_ERROR_ORDER_PUI_DATA_MISSING');
                        $this->_aViewData['pp_pui_error'] = true;
                        return;
                    }
                }/* elseif ($payment->oxpayments__agpaypalpaymentmethod->value != PaymentSource::CARD) {
                    $paypal = new PayPalInitiator(Registry::getConfig()->getCurrentShopUrl() . 'index.php?cl=order&fnc=ppreturn');
                    $paypal->initiate();
                }*/
            }
        }

        return $result;
    }

    public function getPhoneCodes()
    {
        $countryList = oxNew(\OxidEsales\Eshop\Application\Model\CountryList::class);
        $countryList->loadActiveCountries();
        $codesList = \megastruktur\PhoneCountryCodes::getCodesList();
        $phoneCodes = [];

        foreach ($countryList as $country) {
            $phoneCodes[] = [
                'code' => str_replace('+', '', $codesList[strtoupper($country->oxcountry__oxisoalpha2->value)]),
                'country' => $country->oxcountry__oxtitle->value
            ];
        }

        return $phoneCodes;
    }

    public function getUserCountryIsoAlpha2()
    {
        $session = \OxidEsales\Eshop\Core\Registry::getSession();
        $basket = $session->getBasket();
        $user = $basket->getBasketUser();
        $country = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $country->load($user->oxuser__oxcountryid->value);
        return $country->oxcountry__oxisoalpha2->value;
    }

    public function getPayPalClientToken()
    {
        $client = new PayPalRestClient();
        $request = new  GenerateTokenRequest();
        $response = $client->execute($request);
        return $response->client_token;
    }

    protected function getPayment()
    {
        if (!$this->payment) {
            $session = Registry::getSession();
            if (!($paymentId = Registry::getRequest()->getRequestEscapedParameter('paymentid'))) {
                $paymentId = $session->getVariable('paymentid');
            }

            if ($paymentId) {
                $this->payment = oxNew(\OxidEsales\Eshop\Application\Model\Payment::class);
                $this->payment->load($paymentId);
            }
        }
        return $this->payment;
    }

    protected function _unsetPaymentErrors()
    {
        parent::_unsetPaymentErrors();

        $sPayErrorText = Registry::getSession()->getVariable('pperrortext');
        if ($sPayErrorText) {
            Registry::getSession()->deleteVariable('pperrortext');
            $this->_sPaymentErrorText = $sPayErrorText;
        }
    }
}
