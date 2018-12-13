{extends file='parent:frontend/checkout/ajax_add_article.tpl'}

{block name='checkout_ajax_add_actions'}
    {if ({config name="CseEightselectBasicSysAccActive"} && !{config name="CseEightselectBasicPreviewActive"})
        || ({config name="CseEightselectBasicSysAccActive"} && {config name="CseEightselectBasicPreviewActive"} && {$smarty.get.preview})}
        <div data-sku="{$sArticle.ordernumber}" data-include-css="true" data-8select-widget-id="sys-acc"></div>
    {/if}

    {$smarty.block.parent}
{/block}
