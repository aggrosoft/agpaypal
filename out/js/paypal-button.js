function AggrosoftPayPalButton(config) {
  this.config = config;
  this.returnToken = undefined;
  this.shippingId = undefined;
  this.products = undefined;
}

AggrosoftPayPalButton.prototype.render = function() {

  let that = this;

  paypal.Buttons({
    style: Object.assign({
      layout: 'horizontal',
      shape:  'rect',
      label:  'checkout',
      height: 38
    }, that.config.style || {}),
    createOrder: function(data, actions) {
      // Set up the transaction
      return new Promise(function(resolve, reject) {
        $.ajax({
          url: that.config.baseUrl,
          method: 'POST',
          data: {
            cl: that.config.controller,
            fnc: 'createpaypalorder',
            products: that.config.products
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
          method: 'POST',
          data: {
            cl: that.config.controller,
            fnc: 'updatepaypalpurchaseunits',
            ppcountryid: data.shipping_address.country_code,
            sShipSet: data.selected_shipping_option.id,
            token: data.orderID,
            pptoken: that.returnToken
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
      document.location.href = that.config.redirectUrl + "&token=" + data.orderID + "&pptoken=" + that.returnToken + "&shippingid=" + that.shippingId;
    }
  }).render(that.config.container);
};