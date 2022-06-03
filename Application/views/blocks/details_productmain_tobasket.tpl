[{$smarty.block.parent}]
<hr/>
<div id="paypal-button-container-details"></div>

[{assign var="currency" value=$oView->getActCurrency()}]
[{oxscript include="https://www.paypal.com/sdk/js?components=buttons&commit=false&client-id="|cat:$oViewConf->getPayPalClientId()|cat:"&currency="|cat:$currency->name}]
[{oxscript include=$oViewConf->getModuleUrl('agpaypal', 'out/js/paypal-button.js')}]

[{capture assign=pageScript}]
    let detailsPayPalButton = new AggrosoftPayPalButton({
        baseUrl: '[{$oViewConf->getSelfActionLink()}]',
        redirectUrl: '[{$oViewConf->getSelfActionLink()|html_entity_decode}]&cl=order&fnc=ppreturn',
        container: '#paypal-button-container-details',
        controller: 'details',
        style: {
            layout: 'vertical'
        },
        products: [
            {
                id: '[{$oDetailsProduct->oxarticles__oxid->value}]',
                amount: 1
            }
        ]
    })

    detailsPayPalButton.render();
    [{/capture}]
[{oxscript add=$pageScript}]