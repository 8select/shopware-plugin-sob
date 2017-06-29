{block name="widgets_emotion_components_sys_psv"}
    {if {config name="8s_enabled"}}
        <div class="-eightselect-widget-container" style="display: none;">
            <div data-sku="{$Data.sys_psv_ordernumber}" data-8select-widget-id="sys-psv"></div>
        </div>
    {/if}
{/block}
