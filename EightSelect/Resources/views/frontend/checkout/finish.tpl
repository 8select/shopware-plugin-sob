{extends file="parent:frontend/checkout/finish.tpl"}

{block name="frontend_index_content"}
    {$smarty.block.parent}
    <script type="text/javascript"><![CDATA[*/
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
        /*]]>*/</script>
{/block}
