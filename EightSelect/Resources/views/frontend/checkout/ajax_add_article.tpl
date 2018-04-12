{extends file='parent:frontend/checkout/ajax_add_article.tpl'}

{block name='checkout_ajax_add_actions'}
    {if "{config name="8s_sys_acc_enabled"}"}
        <div data-sku="{$sArticle.ordernumber}" data-include-css="true" data-8select-widget-id="sys-acc"></div>
    {/if}

    {$smarty.block.parent}
{/block}
