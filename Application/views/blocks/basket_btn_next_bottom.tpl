[{if $oViewConf->showPayPalButtonInBasket()}]
  [{include file="page/checkout/inc/paypal_express_button.tpl" sButtonPosition="bottom"}]
[{/if}]
[{$smarty.block.parent}]
