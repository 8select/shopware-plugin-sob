{block name="widgets_emotion_components_sys_psv"}
    {if ({config name="CseEightselectBasicPluginActive"} && !{config name="CseEightselectBasicPreviewActive"})
        || ({config name="CseEightselectBasicPluginActive"} && {config name="CseEightselectBasicPreviewActive"} && {$smarty.get.preview})}
        <div class="-eightselect-widget-container" style="display: none;">
            <div
                data-sku="{$Data.sys_psv_ordernumber}"
                data-8select-widget-id="sys-psv"
                {if $Data.sys_psv_lazyload_factor|count_characters > 0 && $Data.sys_psv_lazyload_factor >= 0}
                    data-load-distance-factor="{$Data.sys_psv_lazyload_factor}"
                {/if}
            >
            </div>
        </div>
    {/if}
{/block}
