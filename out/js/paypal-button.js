function AggrosoftPayPalButton(config) {
  this.config = config;
  this.returnToken = undefined;
  this.shippingId = undefined;
}

AggrosoftPayPalButton.prototype.render = function() {

  let that = this;

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
          url: that.config.baseUrl,
          data: {
            cl: 'basket',
            fnc: 'createpaypalorder',
            paymentid: that.config.paymentId
          }
        }).then(function(result) {
          that.returnToken = result.returnToken;
          return resolve(result.orderId);
        }).fail(function() {
          reject(new Error('Die Transaktion konnte aufgrund eines technischen Fehlers nicht gestartet werden.'));
        });
      })
    },
    onShippingChange: function(data, actions) {
      return new Promise(function(resolve, reject) {
        $.ajax({
          url: that.config.baseUrl,
          data: {
            cl: 'basket',
            fnc: 'updatepaypalpurchaseunits',
            ppcountryid: data.shipping_address.country_code,
            sShipSet: data.selected_shipping_option.id
          }
        }).then(function(result){
          if (result) {
            that.shippingId = data.selected_shipping_option.id;
            return resolve();
          }else{
            return reject();
          }
        })
      })
    },
    onApprove: function(data, actions) {
      document.location.href = that.config.redirectUrl + "&token=" + data.orderID + "&pptoken=" + that.returnToken + "&paymentid=" + that.config.paymentId + "&shippingid=" + that.shippingId;
    }
  }).render(that.config.container);
};