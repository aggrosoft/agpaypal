<script type="application/json" fncls="fnparams-dede7cc5-15fd-4c75-a9f4-36c430ee3a99">
    {
        "f":"[{$oViewConf->getFraudNetSessionIdentifier()}]",
        "s":"[{$oViewConf->getFraudNetSourceWebsiteIdentifier()}]",
        "sandbox": [{if $oViewConf->isPayPalSandbox()}]true[{else}]false[{/if}]
    }
    </script>
<script type="text/javascript" src="https://c.paypal.com/da/r/fb.js"></script>
<noscript>
    <img src="https://c.paypal.com/v1/r/d/b/ns?f=[{$oViewConf->getFraudNetSessionIdentifier()}]&s=[{$oViewConf->getFraudNetSourceWebsiteIdentifier()}]&js=0&r=1" />
</noscript>