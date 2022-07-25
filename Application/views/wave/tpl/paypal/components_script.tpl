[{assign var="currency" value=$oView->getActCurrency()}]
[{if $oViewConf->getTopActiveClassName() == 'order' && $oView->getPayPalFunding()}]
    [{assign var=paypalFundings value=$oView->getPayPalFunding()}]
    [{assign var=paypalCommit value="true"}]
[{else}]
    [{assign var=paypalFundings value="paylater,card"}]
    [{assign var=paypalCommit value="false"}]
[{/if}]
<script src="https://www.paypal.com/sdk/js?components=buttons,messages,hosted-fields,funding-eligibility,payment-fields,marks&integration-date=2022-01-06&enable-funding=[{$paypalFundings}]&commit=[{$paypalCommit}]&client-id=[{$oViewConf->getPayPalClientId()}]&currency=[{$currency->name}]"
        [{if $appendPayPalClientToken}]data-client-token="[{$oView->getPayPalClientToken()}]"[{/if}] [{if $oViewConf->getTopActiveClassName() == 'start'}]async[{/if}]
></script>
