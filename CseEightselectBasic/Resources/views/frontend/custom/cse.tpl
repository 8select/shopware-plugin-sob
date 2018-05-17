{if ({config name="8s_enabled"} && !{config name="8s_preview_mode_enabled"})
    || ({config name="8s_enabled"} && {config name="8s_preview_mode_enabled"} && {$smarty.get.preview})}
<div class="-eightselect-widget-container" style="display: none;">
    {include file="string:{$htmlContainer.0}"}
        <div data-sku="{$sArticle.ordernumber}" data-8select-widget-id="sys-psv"></div>
    {include file="string:{$htmlContainer.1}"}
</div>
{/if}
