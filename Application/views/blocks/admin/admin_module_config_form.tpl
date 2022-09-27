[{$smarty.block.parent}]
[{if false && $oModule->getInfo('id') === 'agpaypal'}]
    <script type="text/javascript">
        function onboardedCallback(authCode, sharedId) {
            console.log(authCode, sharedId);
        }
    </script>

    <a target="_blank" data-paypal-onboard-complete="onboardedCallback" href="https://www.sandbox.paypal.com/bizsignup/partner/entry?partnerClientId=[{$oView->getPayPalPartnerClientId()}]&partnerId=[{$oView->getPayPalPartnerId()}]&displayMode=minibrowser&partnerLogoUrl=[{$oView->getPayPalPartnerLogoUrl()|urlencode}]&returnToPartnerUrl=[{$oViewConf->getSelfLink()|urlencode}]&integrationType=FO&features=PAYMENT&country.x=DE&locale.x=de-DE&product=ppcp&secondaryProducts=payment_methods&capabilities=PAY_UPON_INVOICE&sellerNonce=[{$oView->getPayPalSellerNonce()}]" data-paypal-button="PPLtBlue">
        PUI URL Onboarding</a>

    <script id="paypal-js" src="https://www.sandbox.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js"></script>
[{/if}]