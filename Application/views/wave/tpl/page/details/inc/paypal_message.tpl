[{assign var="currency" value=$oView->getActCurrency()}]
[{assign var="oPrice" value=$oDetailsProduct->getPrice()}]

<div
        class="pp-message-details"
        data-pp-message
        data-pp-placement="product"
        data-pp-style-layout="text"
        data-pp-amount="[{$oPrice->getPrice()}]"
        style="height:2em;"
>
</div>