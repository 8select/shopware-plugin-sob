{if ({config name="CseEightselectBasicPluginActive"} && !{config name="CseEightselectBasicPreviewActive"})
    || ({config name="CseEightselectBasicPluginActive"} && {config name="CseEightselectBasicPreviewActive"} && {$smarty.get.preview})}
<div class="-eightselect-widget-container" style="display: none;">
    {include file="string:{$htmlContainer.0}"}
        <div data-sku="{$sArticle.ordernumber}" data-8select-widget-id="sys-psv"></div>
    {include file="string:{$htmlContainer.1}"}
</div>
{/if}
