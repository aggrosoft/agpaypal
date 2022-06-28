<hr/>
<div id="paypal-button-container-details"></div>

[{assign var="currency" value=$oView->getActCurrency()}]

[{capture assign=pageScript}]
    (function(){
        let detailsPayPalButton = new AggrosoftPayPalButton({
            baseUrl: '[{$oViewConf->getSelfActionLink()}]',
            redirectUrl: '[{$oViewConf->getSelfActionLink()|html_entity_decode}]&cl=order&fnc=ppreturn',
            container: '#paypal-button-container-details',
            controller: 'details',
            style: {
                layout: 'vertical'
            },
            beforeCheckout: function(){
                const formData = new FormData($('.js-oxProductForm').get(0));
                let amount = 1;
                const sellists = {};
                const persparam = {};

                for(const pair of formData.entries()) {
                    const key = pair[0];
                    const value = pair[1];
                    const regex = /(\w*)\[(\w*)\]/gm;
                    const match = regex.exec(key);

                    if (key === 'am') {
                        amount = value;
                    }else if ( match && match[1] === 'sel' ) {
                        sellists[match[2]] = value
                    }else if ( match && match[1] === 'persparam' ) {
                        persparam[match[2]] = value
                    }
                }
                detailsPayPalButton.setConfigValue('products', [
                    {
                        id: '[{$oDetailsProduct->oxarticles__oxid->value}]',
                        amount: amount,
                        sellists: sellists,
                        persparam: persparam
                    }
                ]);
            }
        })

        function formDataToJson(f) {
            return Object.fromEntries(Array.from(f.keys(), k =>
            k.endsWith(']') ? [k.slice(0, -3), f.getAll(k)] : [k, f.get(k)]));
        }

        detailsPayPalButton.render();
    }())
    [{/capture}]
[{oxscript add=$pageScript}]