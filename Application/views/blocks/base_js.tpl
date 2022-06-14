[{assign var="currency" value=$oView->getActCurrency()}]

[{if $oViewConf->getTopActiveClassName() == 'payment'}]
  [{assign var=paymentmethod value=$oView->getPayPalCreditCardPaymentMethod()}]
  [{if $paymentmethod}]
    [{assign var=appendPayPalClientToken value=true}]
  [{/if}]
[{/if}]

[{include file="paypal/components_script.tpl"}]
[{include file="paypal/fraudnet_script.tpl"}]

[{$smarty.block.parent}]

[{if $appendPayPalClientToken}]
  [{include file="paypal/hosted_fields.tpl"}]
[{/if}]