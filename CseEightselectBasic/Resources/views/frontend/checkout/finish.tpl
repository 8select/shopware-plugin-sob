{extends file="parent:frontend/checkout/finish.tpl"}

{block name='frontend_index_header_javascript_tracking'}
    {$smarty.block.parent}
    <script type="text/javascript">/*{literal}<![CDATA[*/
            window.eightlytics || function (a) {
                a.eightlytics = function () {
                    (a.eightlytics_queue = []).push(arguments)
                };
                (function (b, a, d) {
                    var c = b.createElement(a);
                    c.type = "text/javascript";
                    c.async = !0;
                    c.src = d;
                    b = b.getElementsByTagName(a)[0];
                    b.parentNode.insertBefore(c, b)
                })(a.document, "script", {/literal}"https://wgt.8select.io/eightlytics/eightlytics-queue.js"{literal})
            }(window);
    /*]]>{/literal}*/</script>
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
{/block}
