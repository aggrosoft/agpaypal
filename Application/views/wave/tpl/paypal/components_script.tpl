[{assign var="currency" value=$oView->getActCurrency()}]

[{if $oViewConf->getTopActiveClassName() === 'details' || $oViewConf->getTopActiveClassName() === 'basket'}]
    [{assign var=paypalCommit value='false'}]
[{else}]
    [{assign var=paypalCommit value='true'}]
[{/if}]

<script src="https://www.paypal.com/sdk/js?components=buttons,messages,hosted-fields,funding-eligibility,payment-fields,marks&integration-date=2022-01-06&intent=capture&enable-funding=paylater,card,giropay,eps,ideal&commit=[{$paypalCommit}]&client-id=[{$oViewConf->getPayPalClientId()}]&currency=[{$currency->name}]"
        [{if $appendPayPalClientToken}]data-client-token="[{$oView->getPayPalClientToken()}]"[{/if}] [{if $oViewConf->getTopActiveClassName() == 'start'}]async[{/if}] data-partner-attribution-id="Oxid_Cart_Aggrosoft_PPCP"
></script>
