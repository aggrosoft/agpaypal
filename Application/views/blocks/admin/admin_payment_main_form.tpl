[{$smarty.block.parent}]

<tr>
    <td class="edittext">
        [{oxmultilang ident="PAYMENT_MAIN_PAYPAL_PAYMENT_METHOD"}]
    </td>
    <td class="edittext">
        <select name="editval[oxpayments__agpaypalpaymentmethod]" class="editinput" [{$readonly}]>
            <option value=""></option>
            [{foreach from=$oView->getPayPalPaymentMethods() item=method}]
            <option value="[{$method}]" [{if $edit->oxpayments__agpaypalpaymentmethod->value === $method}]SELECTED[{/if}]>[{oxmultilang ident='PAYPAL_PAYMENT_METHOD_'|cat:$method}]</option>
            [{/foreach}]
        </select>
        [{oxinputhelp ident="HELP_PAYMENT_MAIN_PAYPAL_PAYMENT_METHOD"}]
    </td>
</tr>

<tr>
    <td class="edittext">
        [{oxmultilang ident="PAYMENT_MAIN_PAYPAL_LANDING_PAGE"}]
    </td>
    <td class="edittext">
        <select name="editval[oxpayments__agpaypallandingpage]" class="editinput" [{$readonly}]>
            <option value=""></option>
            <option value="LOGIN" [{if $edit->oxpayments__agpaypallandingpage->value === 'LOGIN'}]SELECTED[{/if}]>[{oxmultilang ident="PAYPAL_PAYMENT_LANDING_LOGIN"}]</option>
            <option value="BILLING" [{if $edit->oxpayments__agpaypallandingpage->value === 'BILLING'}]SELECTED[{/if}]>[{oxmultilang ident="PAYPAL_PAYMENT_LANDING_BILLING"}]</option>
        </select>
        [{oxinputhelp ident="HELP_PAYMENT_MAIN_PAYPAL_LANDING_PAGE"}]
    </td>
</tr>