[{assign var="currency" value=$oView->getActCurrency()}]

[{if $oViewConf->getTopActiveClassName() === 'details' || $oViewConf->getTopActiveClassName() === 'basket'}]
    [{assign var=paypalCommit value='false'}]
[{else}]
    [{assign var=paypalCommit value='true'}]
[{/if}]

<script src="https://www.paypal.com/sdk/js?components=buttons,messages,hosted-fields,funding-eligibility,payment-fields,marks[{if $oViewConf->getPayPalLocale()}]&locale=[{$oViewConf->getPayPalLocale()}][{/if}]&integration-date=2022-01-06&intent=capture[{if $oViewConf->getPayPalEnabledFundings()}]&enable-funding=[{$oViewConf->getPayPalEnabledFundings()}][{/if}]&commit=[{$paypalCommit}]&client-id=[{$oViewConf->getPayPalClientId()}]&currency=[{$currency->name}][{if $oViewConf->getTopActiveClassName() !== 'order' && $oViewConf->getPayPalDisabledFundings()}]&disable-funding=[{$oViewConf->getPayPalDisabledFundings()}][{/if}]"></script>
        [{if $appendPayPalClientToken}]data-client-token="[{$oView->getPayPalClientToken()}]"[{/if}] [{if $oViewConf->getTopActiveClassName() == 'start'}]async[{/if}] data-partner-attribution-id="Oxid_Cart_Aggrosoft_PPCP"
></script>
