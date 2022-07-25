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
        style: {
            label: 'pay',
            tagline: false
        }
    })

    PayPalButton.render();
    [{/capture}]
    [{oxscript add=$pageScript}]
[{else}]
    [{$smarty.block.parent}]
[{/if}]