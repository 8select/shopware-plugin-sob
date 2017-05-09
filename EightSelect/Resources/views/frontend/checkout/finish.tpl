{extends file="parent:frontend/checkout/finish.tpl"}

{block name="frontend_index_content"}
    {$smarty.block.parent}
    <script type="text/javascript">/*{literal}<![CDATA[*/
        window.eightlytics(
            'purchase', 
            {
                customerid: {/literal}'{$sAddresses.billing.customernumber}'{literal},
                orderid: {/literal}'{$sAddresses.billing.orderID}'{literal},
                products: [
                    {/literal}{foreach $sBasket.content as $key => $sBasketItem}{literal}
                    {
                        sku: '{/literal}{$sBasketItem.ordernumber}{literal}',
                        amount: {/literal}{$sBasketItem.quantity}{literal},
                        // todo: get price as euro cent (integer !!!)
                        price: {/literal}{math equation="x * 100" x=$sBasketItem.price}{literal}
                    },
                    {/literal}{/foreach}{literal}
                ]
            }
        );
        /*]]>{/literal}*/</script>
{/block}
