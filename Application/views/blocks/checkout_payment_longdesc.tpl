[{oxscript add="$('.payment-option.activePayment').show()"}]
[{if $paymentmethod->oxpayments__agpaypalpaymentmethod->value == 'PAY_UPON_INVOICE'}]
<div class="desc">
    <div class="paypal-ratepay-legal">
        [{oxmultilang ident="PAYPAL_RATEPAY_LEGAL"}]
    </div>
    [{if $paymentmethod->oxpayments__oxlongdesc->value|strip_tags|trim}]
    <div class="paypal-invoice-description">
        [{$paymentmethod->oxpayments__oxlongdesc->getRawValue()}]
    </div>
    [{/if}]
    <hr/>
    <div class="form-row">
        <div class="col">
            <label>Telefonnummer</label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <select class="form-control" name="pp_phone_country_code" required>
                        [{foreach from=$oView->getPhoneCodes() item=phoneCode}]
                        <option value="[{$phoneCode.code}]" [{if $pp_phone_code === $phoneCode.code}]selected[{/if}]>+[{$phoneCode.code}] [{$phoneCode.country}]</option>
                        [{/foreach}]
                    </select>
                </div>
                <input type="text" class="form-control" name="pp_phone_number" required value="[{$pp_phone_number}]" pattern="[0-9]{1,14}?">
            </div>
        </div>
        <div class="col">
            <label>Geburtsdatum</label>
            <input type="date" class="form-control" name="pp_birth_date" required value="[{$pp_birth_date}]">
        </div>
    </div>
</div>
[{elseif $paymentmethod->oxpayments__agpaypalpaymentmethod->value == 'CARD'}]
    [{oxstyle include=$oViewConf->getModuleUrl('agpaypal', 'out/css/hosted-fields.css')}]
    <div class="card_container container" id="card-form">
        <div class="row">
            <div class="col-md-6 card-display py-3 card">
                <div class="row">
                    <div class="col-md-6 mb-3 card-number-container">
                        <label for="card-number">[{oxmultilang ident="PAYPAL_CC_CARD_NUMBER"}]</label>
                        <div id="card-number" class="form-control" required></div>
                        <div class="invalid-feedback">
                            [{oxmultilang ident="PAYPAL_CC_CARD_INVALID"}]
                        </div>
                        <div id="card-image"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="expiration-date">[{oxmultilang ident="PAYPAL_CC_EXPIRATION_DATE"}]</label>
                        <div id="expiration-date" class="form-control" required></div>
                        <div class="invalid-feedback">
                            [{oxmultilang ident="PAYPAL_CC_CARD_INVALID"}]
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-9 mb-3">
                        <label for="card-holder-name">[{oxmultilang ident="PAYPAL_CC_CARD_HOLDER"}]</label>
                        <input class="form-control" type="text" id="card-holder-name" name="card-holder-name" autocomplete="off" placeholder="Name wie auf der Karte abgebildet" required value="[{$oxcmp_user->oxuser__oxfname->value}] [{$oxcmp_user->oxuser__oxlname->value}]"/>
                        <div class="invalid-feedback">
                            [{oxmultilang ident="PAYPAL_CC_CARD_INVALID"}]
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="cvv">[{oxmultilang ident="PAYPAL_CC_CARD_CVV"}]</label>
                        <div id="cvv" class="form-control" required></div>
                        <div class="invalid-feedback">
                            [{oxmultilang ident="PAYPAL_CC_CARD_INVALID"}]
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
[{else}]
[{$smarty.block.parent}]
[{/if}]