[{assign var=ppBankData value=$order->getPayPalBankData() }]
[{if $ppBankData}]
    <h3 class="underline">[{oxmultilang ident="BANK_DETAILS"}]</h3>
    <p>
        [{oxmultilang ident="BANK"}] [{$ppBankData->agpaypalbankdata__bankname->value}]<br>
        [{oxmultilang ident="BIC"}] [{$ppBankData->agpaypalbankdata__bic->value}]<br>
        [{oxmultilang ident="IBAN"}] [{$ppBankData->agpaypalbankdata__iban->value}]<br/>
        [{oxmultilang ident="PAYPAL_PUI_REFERENCE"}] [{$ppBankData->agpaypalbankdata__reference->value}]<br/>
    </p>
<br>
[{/if}]