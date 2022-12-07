<?php

namespace Aggrosoft\PayPal\Application\Controller;

use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\GetOrderRequest;
use Aggrosoft\PayPal\Application\Core\Client\Request\Payments\Captures\RefundCapturedPaymentRequest;
use Aggrosoft\PayPal\Application\Core\PayPalBasketHandler;
use Aggrosoft\PayPal\Application\Core\Webhook\WebhookVerifier;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class WebhookController extends \OxidEsales\Eshop\Application\Controller\FrontendController
{
    public function render()
    {
        \Ecomponents\License\LicenseManager::getInstance()->validate('agpaypal');

        $verifier = new WebhookVerifier();
        $data = $verifier->verifyIncomingWebhook();

        if ($data) {
            switch ($data->event_type) {
                case 'PAYMENT.CAPTURE.COMPLETED':
                    $this->handlePaymentCaptureCompleted($data);
                    break;
                case 'CHECKOUT.ORDER.APPROVED':
                    $this->handleCheckoutOrderApproved($data);
                    break;
                case 'PAYMENT.CAPTURE.DENIED':
                    $this->handlePaymentCaptureDenied($data);
                    break;
                case 'CHECKOUT.PAYMENT-APPROVAL.REVERSED':
                    $this->handlePaymentApprovalReversed($data);
                    break;
                case 'PAYMENT.CAPTURE.REVERSED':
                    $this->handlePaymentCaptureReversed($data);
                    break;
                case 'PAYMENT.CAPTURE.REFUNDED':
                    $this->handlePaymentCaptureRefunded($data);
                    break;
            }
        }

        Registry::getUtils()->showMessageAndExit('');
    }

    protected function handlePaymentCaptureCompleted($data)
    {
        $client = new PayPalRestClient();

        $orderId = $data->resource->supplementary_data->related_ids->order_id;
        $order = $this->loadOrderByPayPalToken($orderId);
        $client->logExternal('Handling payment capture completed webhook for order id ' . $orderId);
        if (!$order) {
            $client->logExternal('Order not found, trying to finalize');
            try {
                $order = $this->finalizePayPalOrder($orderId);
            } catch (\Exception $e) {
                $order = null;
            }
        }

        // Still no order and a capture? Refund it - there is no way to handle this without an order
        if (!$order) {
            $client->logExternal('Order not found or created, refund if needed');
            $this->refundPayment($orderId);
            return;
        }

        if ($order && !$order->oxorder__agpaypalcaptureid->value) {
            $client->logExternal('Order needs to be updated, saving data');
            $response = $client->execute(new GetOrderRequest($orderId));

            $capture = $response->purchase_units[0]->payments->captures[0];
            $order->oxorder__oxpaid = new \OxidEsales\Eshop\Core\Field(date("Y-m-d H:i:s"));
            $order->oxorder__agpaypalcaptureid = new \OxidEsales\Eshop\Core\Field($capture->id);
            $order->oxorder__agpaypaltransstatus = new \OxidEsales\Eshop\Core\Field($capture->status);
            if (!$order->save()){
                $client->logExternal('Order could not be saved');
            }

            $client->logExternal('Capture ID ' . $capture->id . ' saved to order ' . $order->getId());
            $client->logExternal('Order status ' . $capture->status . ' saved to order ' . $order->getId());

            if ($response->payment_source->pay_upon_invoice) {
                $client->logExternal('Order is PUI, save bank data');
                $bankData = oxNew(\Aggrosoft\PayPal\Application\Model\PayPalBankData::class);
                if ($bankData->assignPayPalPUIData($order->getId(), $response->payment_source->pay_upon_invoice)) {
                    $bankData->save();
                    $order->sendOrderByEmailForPayPalPUI();
                }
            }
        }
    }

    protected function handlePaymentCaptureDenied($data)
    {
        if (Registry::getConfig()->getConfigParam('blAutoCancelOrders', null, 'module:agpaypal')) {
            $orderId = $data->resource->supplementary_data->related_ids->order_id;
            $order = $this->loadOrderByPayPalToken($orderId);

            if ($order) {
                $order->cancelOrder();
            }
        }
    }

    protected function handlePaymentApprovalReversed($data)
    {
        // this should never happen, keep for later reference
    }

    protected function loadOrderByPayPalToken($token)
    {
        if (class_exists('\OxidEsales\EshopCommunity\Internal\Container\ContainerFactory')) {
            $container = ContainerFactory::getInstance()->getContainer();
            $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);
            $queryBuilder = $queryBuilderFactory->create();

            $data = $queryBuilder->select('oxid')
                ->from('oxorder')
                ->where('oxorder.oxtransid = :token')
                ->setParameter('token', $token)
                ->execute();

            $orderId = $data->fetchColumn();
        } else {
            $rs = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->select('SELECT oxid FROM oxorder WHERE oxorder.oxtransid = :token', ['token' => $token]);
            $orderId = current($rs->getFields());
        }


        if ($orderId) {
            $order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
            $order->load($orderId);
            $order->recalculateOrder();
            return $order;
        }
    }

    protected function handlePaymentCaptureReversed($data)
    {
        //@TODO: Check if needed
    }

    protected function handlePaymentCaptureRefunded($data)
    {
        //@TODO: Check if needed
    }

    protected function handleCheckoutOrderApproved($data)
    {
        $client = new PayPalRestClient();
        $client->logExternal('Handling checkout order approved webhook for order id ' . $data->resource->id);
        $orderId = $data->resource->id;
        $order = $this->loadOrderByPayPalToken($orderId);

        if (!$order) {
            $client->logExternal('Order does not exist, try to finalize');
            $this->finalizePayPalOrder($orderId);
        }
    }

    protected function finalizePayPalOrder($token)
    {
        Registry::getSession()->setVariable('pptoken', $token);
        $userBasket = PayPalBasketHandler::getUserBasketForToken($token);
        if ($userBasket) {
            $user = $userBasket->getBasketUser();
            Registry::getSession()->setVariable('usr', $user->getId());
            $basket = PayPalBasketHandler::restoreBasketFromUserBasket($userBasket, $user);
            $order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
            $order->setValidateDeliveryAddressMD5(false);
            $iSuccess = $order->finalizeOrder($basket, $basket->getBasketUser());
            // performing special actions after user finishes order (assignment to special user groups)
            $user->onOrderExecute($basket, $iSuccess);
            return $order;
        }
    }

    protected function refundPayment($orderId)
    {
        $client = new PayPalRestClient();
        $response = $client->execute(new GetOrderRequest($orderId));
        if ($response) {
            $capture = $response->purchase_units[0]->payments->captures[0];
            $request = new RefundCapturedPaymentRequest($capture->id);
            $client->execute($request);
        }
    }
}
