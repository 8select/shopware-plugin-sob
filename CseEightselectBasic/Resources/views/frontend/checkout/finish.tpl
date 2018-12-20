{extends file="parent:frontend/checkout/finish.tpl"}

{block name='frontend_index_header_javascript_tracking'}
    {$smarty.block.parent}
    {if $isCseWidgetConfigValid}
        <script type="text/javascript">
            window.eightlytics(
                'purchase',
                {ldelim}
                    customerid: '{$sAddresses.billing.customernumber}',
                    orderid: '{$sAddresses.billing.orderID}',
                    products: [
                        {foreach $sBasket.content as $key => $sBasketItem}
                            {ldelim}
                                sku: '{$sBasketItem.ordernumber}',
                                amount: {$sBasketItem.quantity},
                                price: {$sBasketItem.intprice}
                            {rdelim},
                        {/foreach}
                    ]
                {rdelim}
            );
        </script>
    {/if}
{/block}
