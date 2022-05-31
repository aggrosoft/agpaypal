<?php

namespace Aggrosoft\PayPal\Application\Controller;

use Aggrosoft\PayPal\Application\Core\Client\PayPalRestClient;
use Aggrosoft\PayPal\Application\Core\Client\Request\Order\GetOrderRequest;
use Aggrosoft\PayPal\Application\Core\Webhook\WebhookVerifier;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class WebhookController extends \OxidEsales\Eshop\Application\Controller\FrontendController
{
    public function render ()
    {
        $verifier = new WebhookVerifier();
        $data = $verifier->verifyIncomingWebhook();

        if ($data)
        {
            switch($data->event_type) {
                case 'PAYMENT.CAPTURE.COMPLETED':
                    $this->handlePaymentCaptureCompleted($data);
                    break;
                case 'PAYMENT.CAPTURE.DENIED':
                    $this->handlePaymentCaptureDenied($data);
                    break;
                case 'CHECKOUT.PAYMENT-APPROVAL.REVERSED':
                    $this->handlePaymentApprovalReversed($data);
                    break;
            }

            \OxidEsales\Eshop\Core\Registry::getUtils()->showMessageAndExit('');
        }

    }

    protected function handlePaymentCaptureCompleted ($data)
    {
        $orderId = $data->resource->supplementary_data->related_ids->order_id;
        $order = $this->loadOrderByPayPalToken($orderId);

        if ($order && !$order->oxorder__agpaypalcaptureid->value) {
            $client = new PayPalRestClient();
            $response = $client->execute(new GetOrderRequest($orderId));

            $bankData = oxNew(\Aggrosoft\PayPal\Application\Model\PayPalBankData::class);
            $bankData->assignPayPalPUIData($order->getId(), $response->payment_source->pay_upon_invoice);
            $bankData->save();

            $order->sendOrderByEmailForPayPalPUI();

            $capture = $response->purchase_units[0]->payments->captures[0];
            $order->oxorder__oxpaid = new \OxidEsales\Eshop\Core\Field(date("Y-m-d H:i:s"));
            $order->oxorder__agpaypalcaptureid = new \OxidEsales\Eshop\Core\Field($capture->id);
            $order->oxorder__agpaypaltransstatus = new \OxidEsales\Eshop\Core\Field($capture->status);
            $order->save();
        }
    }

    protected function handlePaymentCaptureDenied ($data)
    {
        $orderId = $data->resource->supplementary_data->related_ids->order_id;
        $order = $this->loadOrderByPayPalToken($orderId);

        if ($order) {
            $order->cancelOrder();
        }
    }

    protected function handlePaymentApprovalReversed ($data)
    {
        // this should never happen, keep for later reference
    }

    protected function loadOrderByPayPalToken ($token)
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $queryBuilderFactory = $container->get(QueryBuilderFactoryInterface::class);
        $queryBuilder = $queryBuilderFactory->create();

        $data = $queryBuilder->select('oxid')
            ->from('oxorder')
            ->where('oxorder.oxtransid = :token')
            ->setParameter('token', $token)
            ->execute();

        $orderId = $data->fetchColumn();

        if ($orderId) {
            $order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
            $order->load($orderId);
            return $order;
        }
    }
}