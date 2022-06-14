[{if $oViewConf->showPayPalButtonInBasket()}]
  [{include file="page/checkout/inc/paypal_express_button.tpl" sButtonPosition="top"}]
[{/if}]
[{$smarty.block.parent}]
