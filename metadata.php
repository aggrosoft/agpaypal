<?php

$sMetadataVersion = '2.0';

$aModule = array(
    'id'           => 'agpaypal',
    'title'        => 'Aggrosoft PayPal 2.0',
    'description'  => [
        'de' => 'Integration der PayPal Commerce Platform Zahlungsarten',
        'en' => 'PayPal Commerce Platform payment methods integration',
    ],
    'thumbnail'    => '',
    'version'      => '0.0.0',
    'author'       => 'Aggrosoft GmbH',
    'controllers'  => [
        'aggrosoft_paypal_webhook' => \Aggrosoft\PayPal\Application\Controller\WebhookController::class,
    ],
    'extend'      => [
        \OxidEsales\Eshop\Core\ViewConfig::class => \Aggrosoft\PayPal\Application\Core\ViewConfig::class,
        \OxidEsales\Eshop\Application\Controller\OrderController::class => \Aggrosoft\PayPal\Application\Controller\OrderController::class,
        \OxidEsales\Eshop\Application\Controller\PaymentController::class => \Aggrosoft\PayPal\Application\Controller\PaymentController::class,
        \OxidEsales\Eshop\Application\Controller\Admin\PaymentMain::class => \Aggrosoft\PayPal\Application\Controller\Admin\PaymentMain::class,
        \OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration::class => \Aggrosoft\PayPal\Application\Controller\Admin\ModuleConfiguration::class,
        \OxidEsales\Eshop\Application\Model\UserBasket::class => \Aggrosoft\PayPal\Application\Model\UserBasket::class,
        \OxidEsales\Eshop\Application\Model\PaymentGateway::class => \Aggrosoft\PayPal\Application\Model\PaymentGateway::class,
        \OxidEsales\Eshop\Application\Model\Order::class => \Aggrosoft\PayPal\Application\Model\Order::class,
        \OxidEsales\Eshop\Application\Model\Basket::class => \Aggrosoft\PayPal\Application\Model\Basket::class
    ],
    'events'       => array(
        'onActivate'   => '\Aggrosoft\PayPal\Application\Core\Events::onActivate'
    ),
    'templates'   => [

    ],
    'blocks' => [
        [
            'template' => 'page/checkout/inc/payment_other.tpl',
            'block' => 'checkout_payment_longdesc',
            'file' => '/Application/views/blocks/checkout_payment_longdesc.tpl',
        ],
        [
            'template' => 'layout/base.tpl',
            'block' => 'base_js',
            'file' => '/Application/views/blocks/base_js.tpl',
        ],
        [
            'template' => 'page/checkout/basket.tpl',
            'block' => 'basket_btn_next_top',
            'file' => '/Application/views/blocks/basket_btn_next_top.tpl',
        ],
        [
            'template' => 'email/html/order_cust.tpl',
            'block' => 'email_html_order_cust_paymentinfo',
            'file' => '/Application/views/blocks/email_html_order_cust_paymentinfo.tpl',
        ],
        [
            'template' => 'payment_main.tpl',
            'block' => 'admin_payment_main_form',
            'file' => '/Application/views/blocks/admin/admin_payment_main_form.tpl',
        ],
        [
            'template' => 'order_main.tpl',
            'block' => 'admin_order_main_form_payment',
            'file' => '/Application/views/blocks/admin/admin_order_main_form_payment.tpl',
        ]
    ],
    'settings' => [
        ['group' => 'paypal_main', 'name' => 'sPayPalEmailAddress', 'type' => 'str', 'value' => ''],
        ['group' => 'paypal_main', 'name' => 'sPayPalClientId', 'type' => 'str', 'value' => ''],
        ['group' => 'paypal_main', 'name' => 'sPayPalClientSecret', 'type' => 'str', 'value' => ''],
        ['group' => 'paypal_main', 'name' => 'sPayPalWebhookId', 'type' => 'str', 'value' => ''],
        ['group' => 'paypal_main', 'name' => 'blPayPalSandboxMode', 'type' => 'bool', 'value' => false],
    ],
);
