[{$smarty.block.parent}]
[{if $oModule->getInfo('id') === 'agpaypal'}]

    <a target="_blank" data-paypal-onboard-complete="onboardedCallback" href="https://www.sandbox.paypal.com/bizsignup/partner/entry?partnerClientId=<?php echo $partnerclient; ?>&partnerId=<?php echo $partnerPayer; ?>&displayMode=minibrowser&partnerLogoUrl=<?php echo $logoURL;?>&returnToPartnerUrl=<?php echo $returnURL;?>&integrationType=FO&features=PAYMENT&country.x=DE&locale.x=de-DE&product=ppcp&secondaryProducts=payment_methods&capabilities=PAY_UPON_INVOICE&sellerNonce=<?php echo $sellernonce; ?>" data-paypal-button="PPLtBlue">
        PUI URL Onboarding</a>

    <script id="paypal-js" src="https://www.sandbox.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js"></script>
[{/if}]