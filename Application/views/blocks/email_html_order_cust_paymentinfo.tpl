[{assign var=ppBankData value=$order->getPayPalBankData() }]
[{if $ppBankData}]
[{include file="email/html/paypal_paymentinfo.tpl"}]
[{else}]
[{$smarty.block.parent}]
[{/if}]