[{capture assign=pageScript}]
  $("#payment").on("submit", (event) => {
    if ($('#payment input[name="paymentid"]:checked').val() === '[{$paymentmethod->getId()}]') {
      let allValid = true;
      $('#pp_phone_number').val($('#pp_phone_number').val().replace(/[^0-9]/g, ''))

      if(!$('#pp_phone_number').get(0).checkValidity()){
        $('#pp_phone_number').addClass('is-invalid').get(0).reportValidity();
        allValid = false;
      }else{
        $('#pp_phone_number').addClass('is-valid');
      }
      if(!$('#pp_birth_date').get(0).checkValidity()){
        $('#pp_birth_date').addClass('is-invalid').get(0).reportValidity();
        allValid = false;
      }else{
        $('#pp_birth_date').addClass('is-valid');
      }

      if (!allValid) {
        event.preventDefault();
        return false;
      }
    }
  });
[{/capture}]
[{oxscript add=$pageScript}]