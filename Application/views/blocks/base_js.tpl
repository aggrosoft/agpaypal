[{$smarty.block.parent}]
[{if $oViewConf->getTopActiveClassName() == 'payment'}]
[{assign var=paymentmethod value=$oView->getPayPalCreditCardPaymentMethod()}]
[{if $paymentmethod}]
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
<script
        src="https://www.paypal.com/sdk/js?components=buttons,hosted-fields&client-id=[{$oViewConf->getPayPalClientId()}]"
        data-client-token="[{$oView->getPayPalClientToken()}]"
></script>
<script type="text/javascript">
  if (paypal.HostedFields.isEligible()) {
    // Renders card fields
    paypal.HostedFields.render({
      // Call your server to set up the transaction
      createOrder: () => {
        return $.ajax({
          url: "[{$oViewConf->getSelfActionLink()|html_entity_decode|cat:'cl=payment&fnc=createpaypalorder&paymentid='|cat:$paymentmethod->getId()}]",
        }).then(function(result){
          return result.orderId
        })
      },
      styles: {
        '.valid': {
          color: 'green'
        },
        '.invalid': {
          color: 'red'
        }
      },
      fields: {
        number: {
          selector: "#card-number",
          placeholder: "4111 1111 1111 1111"
        },
        cvv: {
          selector: "#cvv",
          placeholder: "123"
        },
        expirationDate: {
          selector: "#expiration-date",
          placeholder: "MM/JJJJ"
        }
      }
    }).then((cardFields) => {
      document.querySelector("#payment").addEventListener("submit", (event) => {
        if ($('#payment input[name="paymentid"]:checked').val() === '[{$paymentmethod->getId()}]') {
          let state = cardFields.getState();
          event.preventDefault();

          if(!state.fields.number.isValid || !state.fields.cvv.isValid || !state.fields.expirationDate.isValid) {
            return false;
          }

          console.log('submit card fields')
          cardFields
            .submit({
              contingencies: ['3D_SECURE'],
              // Cardholder's first and last name
              cardholderName: document.getElementById("card-holder-name").value,
              // Billing Address
              billingAddress: {
                // Street address, line 1
                streetAddress: "[{$oxcmp_user->oxuser__oxstreet->value}] [{$oxcmp_user->oxuser__oxstreetnr->value}]",
                // Street address, line 2 (Ex: Unit, Apartment, etc.)
                extendedAddress: "[{$oxcmp_user->oxuser__oxaddinfo->value}]",
                // State
                // region: document.getElementById("card-billing-address-state").value,
                // City
                locality: "[{$oxcmp_user->oxuser__oxcity->value}]",
                // Postal Code
                postalCode: "[{$oxcmp_user->oxuser__oxzip->value}]",
                // Country Code
                countryCodeAlpha2: "[{$oView->getUserCountryIsoAlpha2()}]",
              },
            })
            .then((payload) => {
              console.log('3d secure', payload)
              if (payload.liabilityShift === "POSSIBLE") {
                document.querySelector("#payment").submit();
              }
            })
            .catch((err) => {
              alert("Payment could not be captured! " + JSON.stringify(err));
            });
        }
      });
    });
  } else {
    $('#card-form').closest('.well').hide();
  }
</script>
[{/if}]
[{/if}]