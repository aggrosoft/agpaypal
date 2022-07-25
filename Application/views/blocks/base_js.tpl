[{assign var="currency" value=$oView->getActCurrency()}]

[{if $oViewConf->getTopActiveClassName() == 'payment'}]
  [{assign var=creditCardPayment value=$oView->getPayPalCreditCardPaymentMethod()}]
  [{assign var=puiPayment value=$oView->getPayPalPayUponInvoicePaymentMethod()}]
[{/if}]

[{include file="paypal/components_script.tpl" appendPayPalClientToken=$creditCardPayment}]
[{include file="paypal/fraudnet_script.tpl"}]

[{$smarty.block.parent}]

[{if $creditCardPayment}]
  [{include file="paypal/hosted_fields.tpl" paymentmethod=$creditCardPayment}]
[{/if}]
[{if $puiPayment}]
  [{include file="paypal/pui.tpl" paymentmethod=$puiPayment}]
[{/if}]
[{* if $oViewConf->getTopActiveClassName() == 'payment'}]
  [{include file="paypal/marks.tpl"}]
[{/if *}]
