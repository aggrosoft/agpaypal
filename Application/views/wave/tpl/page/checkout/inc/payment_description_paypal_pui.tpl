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