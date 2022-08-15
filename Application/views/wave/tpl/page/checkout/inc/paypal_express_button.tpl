<div id="paypal-button-container-[{$sButtonPosition}]" class="float-left"></div>

[{oxscript include=$oViewConf->getModuleUrl('agpaypal', 'out/js/paypal-button.js')}]

[{capture assign=pageScript}]
    let [{$sButtonPosition}]PayPalButton = new AggrosoftPayPalButton({
        baseUrl: '[{$oViewConf->getSelfActionLink()}]',
        redirectUrl: '[{$oViewConf->getSelfActionLink()|html_entity_decode}]&cl=order&fnc=ppreturn',
        container: '#paypal-button-container-[{$sButtonPosition}]',
        controller: 'basket',
        style: {
            layout: 'vertical',
            label: 'pay',
            shape: 'rect',
            color: 'gold'
        }
    })

    [{$sButtonPosition}]PayPalButton.render();
[{/capture}]
[{oxscript add=$pageScript}]