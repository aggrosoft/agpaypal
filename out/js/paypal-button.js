function AggrosoftPayPalButton(config) {
  this.config = config;
  this.returnToken = undefined;
  this.shippingId = undefined;
}

AggrosoftPayPalButton.prototype.setConfigValue = function(key, value) {
  this.config[key] = value;
}

AggrosoftPayPalButton.prototype.render = function() {

  let that = this;
  const fundingSource = this.config.fundingSource || paypal.FUNDING.PAYPAL;

  const button = paypal.Buttons({
    fundingSource: fundingSource,
    style: Object.assign({
      layout: 'horizontal',
      shape:  'rect',
      label:  'checkout',
      height: 38
    }, that.config.style || {}),
    createOrder: function(data, actions) {
      that.config.beforeCheckout && that.config.beforeCheckout();

      // Set up the transaction
      return new Promise(function(resolve, reject) {
        $.ajax({
          url: that.config.baseUrl,
          method: 'POST',
          data: {
            cl: that.config.controller,
            fnc: 'createpaypalorder',
            products: JSON.stringify(that.config.products)
          }
        }).then(function(result) {
          that.returnToken = result.returnToken;
          return resolve(result.orderId);
        }).fail(function() {
          reject(new Error('Die Transaktion konnte aufgrund eines technischen Fehlers nicht gestartet werden.'));
        });
      })
    },
    onApprove: function(data, actions) {
      let href = that.config.redirectUrl + "&token=" + data.orderID + "&pptoken=" + that.returnToken;
      if (that.shippingId) {
        href += "&shippingId=" + that.shippingId;
      }
      document.location.href = href;
    },
    onCancel: function (data) {
      console.log('OnCancel', data);
    },
    onError: function(err) {
      console.log('OnError', err);
    }
  })

  if (fundingSource === paypal.FUNDING.PAYPAL) {
    button.onShippingChange = function(data, actions) {
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
    }
  }

  if (button.isEligible()) {
    button.render(that.config.container);
  }else{
    console.log('PayPal Button is not eligible');
  }
};