[{$smarty.block.parent}]
[{if $paymentType->oxpayments__agpaypalpaymentmethod->value && $edit->blIsPaid}]
    <tr>
        <td class="edittext" colspan="2">
            [{oxmultilang ident="ORDER_MAIN_PAYPAL_TRANSACTION_ID"}]
        </td>
        <td class="edittext">
            [{$edit->oxorder__oxtransid->value}]
        </td>
    </tr>
    <tr>
        <td class="edittext" colspan="2">
            [{oxmultilang ident="ORDER_MAIN_PAYPAL_TRANSACTION_STATUS"}]
        </td>
        <td class="edittext">
            [{$edit->oxorder__agpaypaltransstatus->value}]
        </td>
    </tr>
[{/if}]