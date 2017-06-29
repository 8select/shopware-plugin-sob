{extends file="parent:frontend/index/header.tpl"}

{block name="frontend_index_header_css_screen"}
    {$smarty.block.parent}
    {if {config name="8s_custom_css"} != ""}
    <style>
        {include file='string:{config name="8s_custom_css"}'}
    </style>
    {/if}
{/block}

{block name='frontend_index_header_javascript_tracking'}
    {$smarty.block.parent}
    {if !$checkoutFinish}
        <script async src="https://widget.{config name="8s_merchant_id"}.8select.io/loader.js"></script>

        <script type="text/javascript">
            if (typeof eightselect_shopware === "undefined") {
                var eightselect_shopware = {}
            }
            eightselect_shopware.addToCart = function (sku) {
                document.getElementById('eightselect_cart_trigger_form_sku').value = sku;
                document.getElementById('eightselect_cart_trigger_form_submit').click();
            }
        </script>
    {/if}
    {if $checkoutFinish}
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
                })(a.document, "script", {/literal}"https://widget.{config name="8s_merchant_id"}.8select.io/eightlytics/eightlytics-queue.js"{literal})
            }(window);
            /*]]>{/literal}*/</script>
    {/if}

{/block}
