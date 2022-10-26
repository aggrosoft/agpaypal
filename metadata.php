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
    'version'      => '1.5.1',
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
        \OxidEsales\Eshop\Application\Model\Payment::class => \Aggrosoft\PayPal\Application\Model\Payment::class,
        \OxidEsales\Eshop\Application\Model\User::class => \Aggrosoft\PayPal\Application\Model\User::class,
        \OxidEsales\Eshop\Application\Model\Address::class => \Aggrosoft\PayPal\Application\Model\Address::class,
        \OxidEsales\Eshop\Application\Model\DeliveryList::class => \Aggrosoft\PayPal\Application\Model\DeliveryList::class,
        \OxidEsales\Eshop\Application\Model\VatSelector::class => \Aggrosoft\PayPal\Application\Model\VatSelector::class,
        \OxidEsales\Eshop\Application\Component\BasketComponent::class => \Aggrosoft\PayPal\Application\Component\BasketComponent::class,
        \InvoicepdfArticleSummary::class => \Aggrosoft\PayPal\Application\Model\InvoicepdfArticleSummary::class,
    ],
    'events'       => array(
        'onActivate'   => '\Aggrosoft\PayPal\Application\Core\Events::onActivate'
    ),
    'templates'   => [
        'email/html/paypal_paymentinfo.tpl'    => 'agpaypal/Application/views/wave/tpl/email/html/paypal_paymentinfo.tpl',
        'page/details/inc/paypal_button.tpl'    => 'agpaypal/Application/views/wave/tpl/page/details/inc/paypal_button.tpl',
        'page/details/inc/paypal_message.tpl'    => 'agpaypal/Application/views/wave/tpl/page/details/inc/paypal_message.tpl',
        'page/checkout/inc/payment_description_paypal_pui.tpl'    => 'agpaypal/Application/views/wave/tpl/page/checkout/inc/payment_description_paypal_pui.tpl',
        'page/checkout/inc/payment_description_paypal_card.tpl'    => 'agpaypal/Application/views/wave/tpl/page/checkout/inc/payment_description_paypal_card.tpl',
        'page/checkout/inc/paypal_express_button.tpl'    => 'agpaypal/Application/views/wave/tpl/page/checkout/inc/paypal_express_button.tpl',
        'page/checkout/inc/select_payment_paypal.tpl'    => 'agpaypal/Application/views/wave/tpl/page/checkout/inc/select_payment_paypal.tpl',
        'page/checkout/inc/payment_paypal.tpl'    => 'agpaypal/Application/views/wave/tpl/page/checkout/inc/payment_paypal.tpl',
        'page/checkout/inc/paypal_message.tpl'    => 'agpaypal/Application/views/wave/tpl/page/checkout/inc/paypal_message.tpl',
        'paypal/components_script.tpl'    => 'agpaypal/Application/views/wave/tpl/paypal/components_script.tpl',
        'paypal/fraudnet_script.tpl'    => 'agpaypal/Application/views/wave/tpl/paypal/fraudnet_script.tpl',
        'paypal/hosted_fields.tpl'    => 'agpaypal/Application/views/wave/tpl/paypal/hosted_fields.tpl',
        'paypal/pui.tpl'    => 'agpaypal/Application/views/wave/tpl/paypal/pui.tpl',
        'paypal/marks.tpl'    => 'agpaypal/Application/views/wave/tpl/paypal/marks.tpl',
    ],
    'blocks' => [
        [
            'template' => 'page/checkout/payment.tpl',
            'block' => 'select_payment',
            'file' => '/Application/views/blocks/select_payment.tpl',
        ],
        [
            'template' => 'page/checkout/payment.tpl',
            'block' => 'checkout_payment_errors',
            'file' => '/Application/views/blocks/checkout_payment_errors.tpl',
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
            'template' => 'page/checkout/inc/basketcontents.tpl',
            'block' => 'checkout_basketcontents_grandtotal',
            'file' => '/Application/views/blocks/checkout_basketcontents_grandtotal.tpl',
        ],
        [
            'template' => 'page/checkout/order.tpl',
            'block' => 'checkout_order_btn_submit_bottom',
            'file' => '/Application/views/blocks/checkout_order_btn_submit_bottom.tpl',
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
        ],
        [
            'template' => 'module_config.tpl',
            'block' => 'admin_module_config_form',
            'file' => '/Application/views/blocks/admin/admin_module_config_form.tpl',
        ]
    ],
    'settings' => [
        ['group' => 'paypal_main', 'name' => 'sPayPalEmailAddress', 'type' => 'str', 'value' => ''],
        ['group' => 'paypal_main', 'name' => 'sPayPalClientId', 'type' => 'str', 'value' => ''],
        ['group' => 'paypal_main', 'name' => 'sPayPalClientSecret', 'type' => 'str', 'value' => ''],
        ['group' => 'paypal_main', 'name' => 'sPayPalWebhookId', 'type' => 'str', 'value' => ''],
        ['group' => 'paypal_main', 'name' => 'blPayPalSandboxMode', 'type' => 'bool', 'value' => false],
        ['group' => 'paypal_settings', 'name' => 'blAutoCancelOrders', 'type' => 'bool', 'value' => false],
        ['group' => 'paypal_settings', 'name' => 'sPayPalLogLevel', 'type' => 'select', 'value' => 'off', 'constraints' => 'off|error|all'],
        ['group' => 'paypal_settings', 'name' => 'blPayPalExpressDetails', 'type' => 'bool', 'value' => false],
        ['group' => 'paypal_settings', 'name' => 'blPayPalExpressBasket', 'type' => 'bool', 'value' => false],
        ['group' => 'paypal_settings', 'name' => 'blPayPalMessagesDetails', 'type' => 'bool', 'value' => false],
        ['group' => 'paypal_settings', 'name' => 'blPayPalMessagesBasket', 'type' => 'bool', 'value' => false],
        ['group' => 'paypal_settings', 'name' => 'blPayPalRedirectOnCheckout', 'type' => 'bool', 'value' => false],
    ],
);
