<script type="text/javascript">
  if (paypal.HostedFields.isEligible()) {
    // Renders card fields
    paypal.HostedFields.render({
      [{if $oViewConf->isPayPalSandbox()}]env: 'sandbox',[{/if}]
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
    },
    onShippingChange: function(data,actions){
      return actions.resolve();
    }
  }).then((cardFields) => {


      cardFields.on('cardTypeChange', function (event) {
        // Change card bg depending on card type
        if (event.cards.length === 1) {
          $('#card-form, #card-image').removeClass('visa master-card maestro american-express discover unionpay jcb diners-club').addClass(event.cards[0].type);

          // Change the CVV length for AmericanExpress cards
          if (event.cards[0].code.size === 4) {
            cardFields.setAttribute({
              field: 'cvv',
              attribute: 'placeholder',
              value: '1234'
            });
          }
        } else {
          cardFields.setAttribute({
            field: 'cvv',
            attribute: 'placeholder',
            value: '123'
          });
        }
      });

      cardFields.on('validityChange', function(event) {
        var field = event.fields[event.emittedBy];

        // Remove any previously applied error or warning classes
        $(field.container).removeClass('is-valid');
        $(field.container).removeClass('is-invalid');

        if (field.isValid) {
          $(field.container).addClass('is-valid');
        } else if (field.isPotentiallyValid) {
          // skip adding classes if the field is
          // not valid, but is potentially valid
        } else {
          $(field.container).addClass('is-invalid');
        }
      });

      $('#card-holder-name').keyup(function() {
        $(this).removeClass('is-valid is-invalid');
        if(this.checkValidity()){
          $(this).addClass('is-valid');
        }else{
          $(this).addClass('is-invalid');
        }
      })


      document.querySelector("#payment").addEventListener("submit", (event) => {
        if ($('#payment input[name="paymentid"]:checked').val() === '[{$paymentmethod->getId()}]') {
          let state = cardFields.getState();
          event.preventDefault();

          let allValid = true;

          if(!state.fields.number.isValid){
            $(state.fields.number.container).addClass('is-invalid');
            allValid = false;
          }else{
            $(state.fields.number.container).addClass('is-valid');
          }

          if(!state.fields.cvv.isValid){
            $(state.fields.cvv.container).addClass('is-invalid');
            allValid = false;
          }else{
            $(state.fields.cvv.container).addClass('is-valid');
          }

          if(!state.fields.expirationDate.isValid){
            $(state.fields.expirationDate.container).addClass('is-invalid');
            allValid = false;
          }else{
            $(state.fields.expirationDate.container).addClass('is-valid');
          }

          if(!$('#card-holder-name').val()){
            $('#card-holder-name').addClass('is-invalid');
            allValid = false;
          }else{
            $('#card-holder-name').addClass('is-valid');
          }

          if (!allValid) {
            return false;
          }

          $('#paymentNextStepBottom').data('original-text', $('#paymentNextStepBottom').html());
          $('#paymentNextStepBottom').html('<i class="fa fa-spinner fa-spin"></i><span> [{oxmultilang ident="PAYPAL_LOADING"}]...</span>').prop('disabled', true);

          cardFields
            .submit({
              [{if !$oViewConf->isPayPalSandbox()}]contingencies: ['3D_SECURE'],[{/if}]
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
              if ([{if $oViewConf->isPayPalSandbox()}]true || [{/if}]payload.liabilityShift === "POSSIBLE") {
                document.querySelector("#payment").submit();
              }else{
                $('#paypalErrorModal').modal('show');
                $('#paymentNextStepBottom').html($('#paymentNextStepBottom').data('original-text')).prop('disabled', false);
              }
            })
            .catch((err) => {
              $('#paypalErrorModal').modal('show');
              $('#paymentNextStepBottom').html($('#paymentNextStepBottom').data('original-text')).prop('disabled', false);
            });
        }
      });
    });
  } else {
    $('#card-form').closest('.well').hide();
  }
</script>

<div class="modal fade" id="paypalErrorModal" tabindex="-1" aria-labelledby="paypalErrorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paypalErrorModalLabel">[{oxmultilang ident="PAYPAL_ERROR_MODAL_TITLE"}]</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="[{oxmultilang ident="CLOSE"}]">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                [{oxmultilang ident="PAYPAL_CC_VALIDATION_ERROR"}]
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">[{oxmultilang ident="PAYPAL_ERROR_MODAL_CONFIRM"}]</button>
            </div>
        </div>
    </div>
</div>