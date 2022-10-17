[{$smarty.block.parent}]
[{if false && $oModule->getInfo('id') === 'agpaypal'}]
[{assign var="sellerNonce" value=$oView->getPayPalSellerNonce()}]
    <script type="text/javascript">
        function onboardedCallback(authCode, sharedId) {
            const form = document.getElementById('moduleConfiguration');
            form.fnc.value='paypalonboarding';
            form.ppauthcode.value = authCode;
            form.ppsharedid.value = sharedId;
            form.submit();
        }
    </script>
    <hr/>
    <input type="hidden" name="ppauthcode" value="" id="ppauthcode">
    <input type="hidden" name="ppsharedid" value="" id="ppsharedid">
    <input type="hidden" name="ppnonce" value="[{$sellerNonce}]" id="ppnonce">

    <a target="_blank" data-paypal-onboard-complete="onboardedCallback" href="https://www.[{if $oView->isPayPalSandbox()}]sandbox.[{/if}]paypal.com/bizsignup/partner/entry?partnerClientId=[{$oView->getPayPalPartnerClientId()}]&partnerId=[{$oView->getPayPalPartnerId()}]&displayMode=minibrowser&partnerLogoUrl=[{$oView->getPayPalPartnerLogoUrl()|urlencode}]&returnToPartnerUrl=[{$oViewConf->getSelfLink()|urlencode}]&integrationType=FO&features=PAYMENT&country.x=DE&locale.x=de-DE&product=ppcp&secondaryProducts=payment_methods&capabilities=PAY_UPON_INVOICE&sellerNonce=[{$sellerNonce}]" data-paypal-button="PPLtBlue">
        PayPal Konto verknüpfen</a>

    <script id="paypal-js" src="https://www.[{if $oView->isPayPalSandbox()}]sandbox.[{/if}]paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js"></script>
[{/if}]