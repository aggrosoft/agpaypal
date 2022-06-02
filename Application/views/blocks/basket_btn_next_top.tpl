[{assign var="currency" value=$oView->getActCurrency()}]
<script src="https://www.paypal.com/sdk/js?components=buttons&client-id=[{$oViewConf->getPayPalClientId()}]&currency=[{$currency->name}]&commit=false"></script>
<div id="paypal-button-container-top" class="float-left"></div>
<script>
  var returnToken;
  var shippingId;
  paypal.Buttons({
    style: {
      layout: 'horizontal',
      shape:  'rect',
      label:  'checkout',
      height: 38
    },
    createOrder: function(data, actions) {
      // Set up the transaction
      return new Promise(function(resolve, reject) {
        $.ajax({
          url: "[{$oViewConf->getSelfActionLink()|html_entity_decode|cat:'cl=basket&fnc=createpaypalorder&paymentid='|cat:$oViewConf->getPayPalPaymentId()}]",
        }).then(function(result) {
          returnToken = result.returnToken;
          return resolve(result.orderId);
        }).fail(function() {
          reject(new Error('Die Transaktion konnte aufgrund eines technischen Fehlers nicht gestartet werden.'));
        });
      })
    },
    onShippingChange: function(data, actions) {
      console.log(data)
      return new Promise(function(resolve, reject) {
        $.ajax({
          url: "[{$oViewConf->getSelfActionLink()}]",
          data: {
            cl: 'payment',
            fnc: 'updatepaypalpurchaseunits',
            ppcountryid: data.shipping_address.country_code,
            sShipSet: data.selected_shipping_option.id
          }
        }).then(function(result){
          if (result) {
            shippingId = data.selected_shipping_option.id;
            return resolve();
          }else{
            return reject();
          }
        })
      })
    },
    onApprove: function(data, actions) {
      console.log(data)
      document.location.href = "[{$oViewConf->getSelfActionLink()|html_entity_decode}]&cl=order&fnc=ppreturn&token=" + data.orderID + "&pptoken=" + returnToken + "&paymentid=[{$oViewConf->getPayPalPaymentId()}]" + "&shippingid="+shippingId;
    }
  }).render('#paypal-button-container-top');
</script>
[{$smarty.block.parent}]