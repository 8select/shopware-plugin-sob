{block name="widgets_emotion_components_psp_psv"}
    {if {config name="8s_enabled"}}
        <div class="-eightselect-widget-container" style="display: none;">
            <div 
                data-set-id="{$Data.psp_psv_set_id}" 
                data-8select-widget-id="psp-psv"
                {if $Data.psp_psv_lazyload_factor && $Data.psp_psv_lazyload_factor >= 0}
                    data-load-distance-factor="{$Data.psp_psv_lazyload_factor}"
                {/if}
            ></div>
        </div>
    {/if}
{/block}
