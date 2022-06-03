<div id="paypal-button-container-bottom" class="float-left"></div>

[{assign var="currency" value=$oView->getActCurrency()}]
[{oxscript include="https://www.paypal.com/sdk/js?components=buttons&commit=false&client-id="|cat:$oViewConf->getPayPalClientId()|cat:"&currency="|cat:$currency->name}]
[{oxscript include=$oViewConf->getModuleUrl('agpaypal', 'out/js/paypal-button.js')}]

[{capture assign=pageScript}]
  let bottomPayPalButton = new AggrosoftPayPalButton({
    baseUrl: '[{$oViewConf->getSelfActionLink()}]',
    redirectUrl: '[{$oViewConf->getSelfActionLink()|html_entity_decode}]&cl=order&fnc=ppreturn',
    container: '#paypal-button-container-bottom',
    controller: 'basket'
  })

  bottomPayPalButton.render();
[{/capture}]
[{oxscript add=$pageScript}]
[{$smarty.block.parent}]