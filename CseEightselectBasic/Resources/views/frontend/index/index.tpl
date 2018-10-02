{extends file="parent:frontend/index/index.tpl"}

{block name="frontend_index_after_body"}
    {$smarty.block.parent}
    {if !$checkoutFinish}
        <form style="display:none;" id="eightselect_cart_trigger_form" method="post" action="{url controller=checkout action=addArticle}" data-add-article="true" data-eventName="submit" {if $theme.offcanvasCart} data-showModal="false" data-addArticleUrl="{url controller=checkout action=ajaxAddArticleCart}"{/if}>
            <input id="eightselect_cart_trigger_form_sku" type="hidden" name="sAdd" value="placeholder">
            <input id="eightselect_cart_trigger_form_quantity" type="hidden" name="sQuantity" value="1">
            <button id="eightselect_cart_trigger_form_submit"></button>
        </form>
    {/if}
{/block}
