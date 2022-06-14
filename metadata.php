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
    'version'      => '1.0.1',
    'author'       => 'Aggrosoft GmbH',
    'controllers'  => [
        'aggrosoft_paypal_webhook' => \Aggrosoft\PayPal\Application\Controller\WebhookController::class,
    ],
    'extend'      => [
        \OxidEsales\Eshop\Core\ViewConfig::class => \Aggrosoft\PayPal\Application\Core\ViewConfig::class,
        \OxidEsales\Eshop\Application\Controller\BasketController::class => \Aggrosoft\PayPal\Application\Controller\BasketController::class,
        \OxidEsales\Eshop\Application\Controller\OrderController::class => \Aggrosoft\PayPal\Application\Controller\OrderController::class,
        \OxidEsales\Eshop\Application\Controller\PaymentController::class => \Aggrosoft\PayPal\Application\Controller\PaymentController::class,
        \OxidEsales\Eshop\Application\Controller\ArticleDetailsController::class => \Aggrosoft\PayPal\Application\Controller\ArticleDetailsController::class,
        \OxidEsales\Eshop\Application\Controller\Admin\PaymentMain::class => \Aggrosoft\PayPal\Application\Controller\Admin\PaymentMain::class,
        \OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration::class => \Aggrosoft\PayPal\Application\Controller\Admin\ModuleConfiguration::class,
        \OxidEsales\Eshop\Application\Controller\Admin\OrderArticle::class => \Aggrosoft\PayPal\Application\Controller\Admin\OrderArticle::class,
        \OxidEsales\Eshop\Application\Model\UserBasket::class => \Aggrosoft\PayPal\Application\Model\UserBasket::class,
        \OxidEsales\Eshop\Application\Model\PaymentGateway::class => \Aggrosoft\PayPal\Application\Model\PaymentGateway::class,
        \OxidEsales\Eshop\Application\Model\Order::class => \Aggrosoft\PayPal\Application\Model\Order::class,
        \OxidEsales\Eshop\Application\Model\OrderArticle::class => \Aggrosoft\PayPal\Application\Model\OrderArticle::class,
        \OxidEsales\Eshop\Application\Model\Basket::class => \Aggrosoft\PayPal\Application\Model\Basket::class,
        \OxidEsales\Eshop\Application\Component\BasketComponent::class => \Aggrosoft\PayPal\Application\Component\BasketComponent::class,
        \InvoicepdfArticleSummary::class => \Aggrosoft\PayPal\Application\Model\InvoicepdfArticleSummary::class,
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
            'template' => 'page/checkout/basket.tpl',
            'block' => 'basket_btn_next_bottom',
            'file' => '/Application/views/blocks/basket_btn_next_bottom.tpl',
        ],
        [
            'template' => 'page/details/inc/productmain.tpl',
            'block' => 'details_productmain_tobasket',
            'file' => '/Application/views/blocks/details_productmain_tobasket.tpl',
        ],
        [
            'template' => 'page/details/inc/productmain.tpl',
            'block' => 'details_productmain_price_value',
            'file' => '/Application/views/blocks/details_productmain_price_value.tpl',
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
        ['group' => 'paypal_settings', 'name' => 'blPayPalSandboxMode', 'type' => 'bool', 'value' => false],
        ['group' => 'paypal_settings', 'name' => 'blAutoCancelOrders', 'type' => 'bool', 'value' => false],
        ['group' => 'paypal_settings', 'name' => 'sPayPalLogLevel', 'type' => 'select', 'value' => 'off', 'constraints' => 'off|error|all'],
        ['group' => 'paypal_settings', 'name' => 'blPayPalExpressDetails', 'type' => 'bool', 'value' => false],
        ['group' => 'paypal_settings', 'name' => 'blPayPalExpressBasket', 'type' => 'bool', 'value' => false],
        ['group' => 'paypal_settings', 'name' => 'blPayPalMessagesDetails', 'type' => 'bool', 'value' => false],
    ],
);
