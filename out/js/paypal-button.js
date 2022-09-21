function AggrosoftPayPalButton(config) {
  this.config = config;
  this.returnToken = undefined;
  this.shippingId = undefined;
  this.orderId = undefined;
}

AggrosoftPayPalButton.prototype.setConfigValue = function(key, value) {
  this.config[key] = value;
}

AggrosoftPayPalButton.prototype.render = function() {

  let that = this;
  const fundingSource = this.config.fundingSource;

  const onApprove = function(data, actions) {
    console.log('onApprove', data, actions);
    let href = that.config.redirectUrl + "&token=" + data.orderID + "&pptoken=" + that.returnToken;
    if (that.shippingId) {
      href += "&shippingId=" + that.shippingId;
    }
    document.location.href = href;
  }

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
      if (that.orderId) {
        return that.orderId;
      }
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
          that.orderId = result.orderId;
          return resolve(result.orderId);
        }).fail(function() {
          reject(new Error('Die Transaktion konnte aufgrund eines technischen Fehlers nicht gestartet werden.'));
        });
      })
    },
    onApprove: onApprove,
    onCancel: function (data) {
      if (that.config.onCancel && data.orderID) {
        const loadingOverlay = $('<div class="paypal-loading-overlay" style="position:fixed; top: 0; left: 0; z-index: 99999999; background-color: rgba(0,0,0,0.8); width: 100%; height: 100%;"><img src="/modules/agpaypal/out/image/loader.svg" width="60" class="paypal-loading-spinner" style="position: absolute; left: 50%; top: 50%; margin-left: -30px; margin-top: -30px;"/></div>');
        loadingOverlay.appendTo('body');
        setTimeout(function() {
          $.ajax({
            url: that.config.baseUrl,
            method: 'POST',
            data: {
              cl: that.config.controller,
              fnc: 'getpaypalorder',
              orderid: data.orderID
            }
          }).then(function(result) {
            const order = JSON.parse(result);
            loadingOverlay.remove();
            if (order.status === 'APPROVED') {
              onApprove(data);
            }else{
              that.config.onCancel(order);
            }

          })
        }, 4000);
      }
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