[{oxscript add="$('.payment-option.activePayment').show()"}]
[{if $paymentmethod->oxpayments__agpaypalpaymentmethod->value == 'PAY_UPON_INVOICE'}]
<div class="desc">
    <div class="paypal-ratepay-legal">
        [{oxmultilang ident="PAYPAL_RATEPAY_LEGAL"}]
    </div>
    [{if $paymentmethod->oxpayments__oxlongdesc->value|strip_tags|trim}]
    <div class="paypal-invoice-description">
        [{$paymentmethod->oxpayments__oxlongdesc->getRawValue()}]
    </div>
    [{/if}]
    <hr/>
    <div class="form-row">
        <div class="col">
            <label>Telefonnummer</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <select class="form-control" name="pp_phone_country_code" required>
                        [{foreach from=$oView->getPhoneCodes() item=phoneCode}]
                        <option value="[{$phoneCode.code}]" [{if $pp_phone_code === $phoneCode.code}]selected[{/if}]>+[{$phoneCode.code}] [{$phoneCode.country}]</option>
                        [{/foreach}]
                    </select>
                </div>
                <input type="text" class="form-control" name="pp_phone_number" required value="[{$pp_phone_number}]" pattern="[0-9]{1,14}?">
            </div>
        </div>
        <div class="col">
            <label>Geburtsdatum</label>
            <input type="date" class="form-control" name="pp_birth_date" required value="[{$pp_birth_date}]">
        </div>
    </div>
</div>
[{elseif $paymentmethod->oxpayments__agpaypalpaymentmethod->value == 'CARD'}]
    <div class="card_container row" id="card-form">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="card-number">Kartennummer</label>
                    <div id="card-number" class="form-control" required></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="expiration-date">Ablaufdatum</label>
                    <div id="expiration-date" class="form-control" required></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9 mb-3">
                    <label for="card-holder-name">Karteninhaber</label>
                    <input class="form-control" type="text" id="card-holder-name" name="card-holder-name" autocomplete="off" placeholder="Name wie auf der Karte abgebildet" required value="[{$oxcmp_user->oxuser__oxfname->value}] [{$oxcmp_user->oxuser__oxlname->value}]"/>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="cvv">CVV</label>
                    <div id="cvv" class="form-control" required></div>
                </div>
            </div>
        </div>
    </div>
    <script
            src="https://www.paypal.com/sdk/js?components=buttons,hosted-fields&client-id=[{$oView->getPayPalClientId()}]"
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

              console.log(state)

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
        // Hides card fields if the merchant isn't eligible
        document.querySelector("#card-form").style = 'display: none';
      }
    </script>
[{else}]
[{$smarty.block.parent}]
[{/if}]