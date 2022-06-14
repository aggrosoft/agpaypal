[{assign var="currency" value=$oView->getActCurrency()}]
<script src="https://www.paypal.com/sdk/js?components=buttons,messages,hosted-fields&commit=false&client-id=[{$oViewConf->getPayPalClientId()}]&currency=[{$currency->name}]"
        [{if $appendPayPalClientToken}]data-client-token="[{$oView->getPayPalClientToken()}]"[{/if}] [{if $oViewConf->getTopActiveClassName() == 'start'}]async[{/if}]
></script>
