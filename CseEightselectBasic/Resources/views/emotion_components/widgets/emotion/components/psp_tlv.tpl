{block name="widgets_emotion_components_psp_tlv"}
    {if ($isCseWidgetConfigValid && !{config name="CseEightselectBasicPreviewActive"})
        || ($isCseWidgetConfigValid && {config name="CseEightselectBasicPreviewActive"} && {$smarty.get.preview})}
        <div class="-eightselect-widget-container" style="display: none;">
            <div 
                data-tags="{$Data.psp_tlv_tags}" 
                data-8select-widget-id="psp-tlv"
                {if $Data.psp_tlv_lazyload_factor|count_characters > 0 && $Data.psp_tlv_lazyload_factor >= 0}
                    data-load-distance-factor="{$Data.psp_tlv_lazyload_factor}"
                {/if}
            >
            </div>
        </div>
    {/if}
{/block}
