[{if $oView->mustRenderPayPalButton() }]
    <div id="paypal-button-container"></div>

    [{oxscript include=$oViewConf->getModuleUrl('agpaypal', 'out/js/paypal-button.js')}]

    [{capture assign=pageScript}]
    let PayPalButton = new AggrosoftPayPalButton({
        baseUrl: '[{$oViewConf->getSelfActionLink()}]',
        fundingSource: "[{$oView->getPayPalFunding()}]",
        redirectUrl: '[{$oViewConf->getSelfActionLink()|html_entity_decode}]&cl=order&fnc=ppreturn&execute=1&sDeliveryAddressMD5=[{$oView->getDeliveryAddressMD5()}]',
        container: '#paypal-button-container',
        controller: 'order',
        style: [{$oViewConf->getPayPalButtonStyleOrder()}],
        onCancel: function (order) {
            window.location.href = '[{$oViewConf->getSelfActionLink()|html_entity_decode}]&cl=payment&payerror=69&payerrortext=[{oxmultilang ident="PAYPAL_PAYMENT_CANCELED"}]';
        },
    })

    PayPalButton.render();
    [{/capture}]
    [{oxscript add=$pageScript}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]