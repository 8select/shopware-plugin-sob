{extends file="parent:frontend/index/index.tpl"}

{block name="frontend_index_after_body"}
    {$smarty.block.parent}
    {if $isCseWidgetConfigValid && !$checkoutFinish}
        <form style="display:none;" id="eightselect_cart_trigger_form" method="post" action="{url controller=checkout action=addArticle}" data-eventName="submit" {if $theme.offcanvasCart} data-showModal="false" data-addArticleUrl="{url controller=checkout action=ajaxAddArticleCart}"{/if}>
            <input id="eightselect_cart_trigger_form_sku" type="hidden" name="" value="placeholder">
            <input id="eightselect_cart_trigger_form_quantity" type="hidden" name="" value="1">
            <button id="eightselect_cart_trigger_form_submit">{s namespace="frontend/forms/elements" name='SupportActionSubmit'}Senden{/s}</button>
        </form>
    {/if}
{/block}
