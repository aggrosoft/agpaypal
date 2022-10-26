[{assign var="iPayError" value=$oView->getPaymentError()}]
[{if $iPayError == 69}]
    <div class="alert alert-danger">[{$oView->getPaymentErrorText()}]</div>
[{else}]
    [{$smarty.block.parent}]
[{/if}]