{block name="widgets_emotion_components_psp_psv"}
    {if ($isCseWidgetConfigValid && !{$CseEightselectBasicPreviewActive})
        || ($isCseWidgetConfigValid&& {$CseEightselectBasicPreviewActive} && {$smarty.get.preview})}
        <div class="-eightselect-widget-container" style="display: none;">
            <div
                data-set-id="{$Data.psp_psv_set_id}"
                data-8select-widget-id="psp-psv"
                {if $Data.psp_psv_lazyload_factor|count_characters > 0 && $Data.psp_psv_lazyload_factor >= 0}
                    data-load-distance-factor="{$Data.psp_psv_lazyload_factor}"
                {/if}
            ></div>
        </div>
    {/if}
{/block}
