[{assign var="currency" value=$oView->getActCurrency()}]
<script src="https://www.paypal.com/sdk/js?components=buttons,messages,hosted-fields&integration-date=2022-01-06&enable-funding=paylater&commit=false&client-id=[{$oViewConf->getPayPalClientId()}]&currency=[{$currency->name}]"
        [{if $appendPayPalClientToken}]data-client-token="[{$oView->getPayPalClientToken()}]"[{/if}] [{if $oViewConf->getTopActiveClassName() == 'start'}]async[{/if}]
></script>
