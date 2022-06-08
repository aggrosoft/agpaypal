[{$smarty.block.parent}]

[{if $oPayments}]
    [{foreach from=$oPayments key=sPaymentId item=oPayment}]
        [{if $edit->oxorder__oxpaymenttype->value == $sPaymentId}]
            [{assign var=paymentType value=$oPayment}]
        [{/if}]
    [{/foreach}]
[{/if}]

[{if $paymentType->oxpayments__agpaypalpaymentmethod->value}]
    <tr>
        <td class="edittext">
            [{oxmultilang ident="ORDER_MAIN_PAYPAL_TRANSACTION_ID"}]
        </td>
        <td class="edittext">
            [{$edit->oxorder__oxtransid->value}]
        </td>
    </tr>
    <tr>
        <td class="edittext">
            [{oxmultilang ident="ORDER_MAIN_PAYPAL_CAPTURE_ID"}]
        </td>
        <td class="edittext">
            [{$edit->oxorder__agpaypalcaptureid->value}]

            [{if $oViewConf->isPayPalSandbox()}]
                [{assign var=detailLink value="https://www.sandbox.paypal.com/activity/payment/"|cat:$edit->oxorder__agpaypalcaptureid->value}]
            [{else}]
                [{assign var=detailLink value="https://www.paypal.com/activity/payment/"|cat:$edit->oxorder__agpaypalcaptureid->value}]
            [{/if}]
            <a href="[{$detailLink}]" target="_blank" rel="noopener">
                ([{oxmultilang ident="ORDER_MAIN_PAYPAL_SHOW_TRANSACTION_DETAILS"}])
            </a>
        </td>
    </tr>
    <tr>
        <td class="edittext">
            [{oxmultilang ident="ORDER_MAIN_PAYPAL_TRANSACTION_STATUS"}]
        </td>
        <td class="edittext">
            [{$edit->oxorder__agpaypaltransstatus->value}]
        </td>
    </tr>
[{/if}]
