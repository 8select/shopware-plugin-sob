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
{/block}
