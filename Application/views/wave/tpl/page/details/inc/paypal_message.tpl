[{assign var="currency" value=$oView->getActCurrency()}]
[{assign var="oPrice" value=$oDetailsProduct->getPrice()}]

<div
        data-pp-message
        data-pp-placement="product"
        data-pp-style-layout="text"
        data-pp-amount="[{$oPrice->getPrice()}]"
        style="height:2em;"
>
</div>