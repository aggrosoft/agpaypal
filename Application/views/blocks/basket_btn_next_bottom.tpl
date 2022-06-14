[{if $oViewConf->showPayPalButtonInBasket()}]
<div id="paypal-button-container-bottom" class="float-left"></div>

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
  [{/if}]
[{$smarty.block.parent}]
