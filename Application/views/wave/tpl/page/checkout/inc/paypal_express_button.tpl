<tr>
    <td colspan="2" id="paypalButtons">
        <div id="paypal-button-container-[{$sButtonPosition}]"></div>
[{oxscript include=$oViewConf->getModuleUrl('agpaypal', 'out/js/paypal-button.js')}]

[{capture assign=pageScript}]
    let [{$sButtonPosition}]PayPalButton = new AggrosoftPayPalButton({
        baseUrl: '[{$oViewConf->getSelfActionLink()}]',
        redirectUrl: '[{$oViewConf->getSelfActionLink()|html_entity_decode}]&cl=order&fnc=ppreturn',
        container: '#paypal-button-container-[{$sButtonPosition}]',
        controller: 'basket',
        style: [{$oViewConf->getPayPalButtonStyleBasket()}],
    })

    [{$sButtonPosition}]PayPalButton.render();
[{/capture}]
[{oxscript add=$pageScript}]

    </td>
</tr>