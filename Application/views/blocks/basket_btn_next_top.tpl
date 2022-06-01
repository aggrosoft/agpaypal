[{assign var="currency" value=$oView->getActCurrency()}]
<script src="https://www.paypal.com/sdk/js?components=buttons&client-id=[{$oViewConf->getPayPalClientId()}]&currency=[{$currency->name}]"></script>
<div id="paypal-button-container-top"></div>
<script>
  var returnToken;
  var shippingId;
  paypal.Buttons({
    createOrder: function(data, actions) {
      // Set up the transaction
      return $.ajax({
        url: "[{$oViewConf->getSelfActionLink()|html_entity_decode|cat:'cl=payment&fnc=createpaypalorder&paymentid='|cat:$oViewConf->getPayPalPaymentId()}]",
      }).then(function(result) {
        returnToken = result.returnToken;
        return result.orderId;
      })
    },
    onShippingChange: function(data, actions) {
      console.log(data)
      return new Promise(function(resolve, reject) {
        $.ajax({
          url: "[{$oViewConf->getSelfActionLink()}]",
          data: {
            cl: 'payment',
            fnc: 'getpaypalpurchaseunits',
            ppcountryid: data.shipping_address.country_code,
            sShipSet: data.selected_shipping_option.id
          }
        }).then(function(result){
          if (result.shipping.options.length) {
            return actions.order.patch([
              {
                op: 'replace',
                path: '/purchase_units/@reference_id==\'default\'/amount',
                value: result.amount
              },
              {
                op: 'replace',
                path: '/purchase_units/@reference_id==\'default\'/shipping/options',
                value: result.shipping.options
              }
            ]).then(function() {
              shippingId = data.selected_shipping_option.id;
              return resolve();
            })
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