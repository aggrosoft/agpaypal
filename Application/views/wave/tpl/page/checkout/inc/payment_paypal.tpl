<dl>
    <dt>
        <input id="payment_[{$sPaymentID}]" type="radio" name="paymentid" value="[{$sPaymentID}]" [{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value}]checked[{/if}]>
        <label for="payment_[{$sPaymentID}]">
            [{if $paymentmethod->getPayPalPaymentIcon() }]
                <img src="[{$paymentmethod->getPayPalPaymentIcon() }]" class="img-responsive paypal-payment-icon" alt="[{$paymentmethod->oxpayments__oxdesc->value}]" />
            [{/if}]
            <b>[{$paymentmethod->oxpayments__oxdesc->value}]</b>
            <div class="paypal-mark-container" data-funding="[{$paymentmethod->oxpayments__agpaypalpaymentmethod->value}]"></div>
        </label>
    </dt>
    <dd class="payment-option[{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value}] activePayment[{/if}]">
        [{if $paymentmethod->getPrice()}]
            [{assign var="oPaymentPrice" value=$paymentmethod->getPrice() }]
            [{if $oViewConf->isFunctionalityEnabled('blShowVATForPayCharge') }]
                [{strip}]
                    ([{oxprice price=$oPaymentPrice->getNettoPrice() currency=$currency}]
                    [{if $oPaymentPrice->getVatValue() > 0}]
                        [{oxmultilang ident="PLUS_VAT"}] [{oxprice price=$oPaymentPrice->getVatValue() currency=$currency}]
                    [{/if}])
                [{/strip}]
            [{else}]
                ([{oxprice price=$oPaymentPrice->getBruttoPrice() currency=$currency}])
            [{/if}]
        [{/if}]

        [{* paypal payment methods do not need dyn values
        foreach from=$paymentmethod->getDynValues() item=value name=PaymentDynValues}]
            <div class="form-group">
                <label class="control-label col-lg-3" for="[{$sPaymentID}]_[{$smarty.foreach.PaymentDynValues.iteration}]">[{$value->name}]</label>
                <div class="col-lg-9">
                    <input id="[{$sPaymentID}]_[{$smarty.foreach.PaymentDynValues.iteration}]" type="text" class="form-control textbox" size="20" maxlength="64" name="dynvalue[[{$value->name}]]" value="[{$value->value}]">
                </div>
            </div>
        [{/foreach *}]

        <div class="clearfix"></div>

        [{block name="checkout_payment_longdesc"}]
            [{oxscript add="$('.payment-option.activePayment').show();"}]
            [{if $paymentmethod->oxpayments__agpaypalpaymentmethod->value == 'PAY_UPON_INVOICE'}]
                [{include file="page/checkout/inc/payment_description_paypal_pui.tpl"}]
            [{elseif $paymentmethod->oxpayments__agpaypalpaymentmethod->value == 'CARD'}]
                [{include file="page/checkout/inc/payment_description_paypal_card.tpl"}]
            [{else}]
                [{if $paymentmethod->oxpayments__oxlongdesc->value|strip_tags|trim}]
                    <div class="desc">
                        [{$paymentmethod->oxpayments__oxlongdesc->getRawValue()}]
                    </div>
                [{/if}]
            [{/if}]
        [{/block}]
    </dd>
</dl>