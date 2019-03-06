{extends file="parent:frontend/detail/index.tpl"}

{block name='frontend_detail_index_header'}
    {if $isCseWidgetConfigValid}
        {if {config name="CseEightselectBasicSysPsvBlock"} == "frontend_detail_index_header" && {config name="CseEightselectBasicSysPsvPosition"}=="widget_before"}
            {include file="frontend/detail/custom/cse.tpl"}
        {/if}
        {$smarty.block.parent}
        {if {config name="CseEightselectBasicSysPsvBlock"} == "frontend_detail_index_header" && {config name="CseEightselectBasicSysPsvPosition"}=="widget_after"}
            {include file="frontend/detail/custom/cse.tpl"}
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_detail_index_detail"}
    {if $isCseWidgetConfigValid}
        {if {config name="CseEightselectBasicSysPsvBlock"} == "frontend_detail_index_detail" && {config name="CseEightselectBasicSysPsvPosition"}=="widget_before"}
            {include file="frontend/detail/custom/cse.tpl"}
        {/if}
        {$smarty.block.parent}
        {if {config name="CseEightselectBasicSysPsvBlock"} == "frontend_detail_index_detail" && {config name="CseEightselectBasicSysPsvPosition"}=="widget_after"}
            {include file="frontend/detail/custom/cse.tpl"}
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{* Crossselling tab panel *}
{block name="frontend_detail_index_tabs_cross_selling"}
    {if $isCseWidgetConfigValid}
        {if {config name="CseEightselectBasicSysPsvBlock"} == "frontend_detail_index_tabs_cross_selling" && {config name="CseEightselectBasicSysPsvPosition"}=="widget_before"}
            {include file="frontend/detail/custom/cse.tpl"}
        {/if}
        {$smarty.block.parent}
        {if {config name="CseEightselectBasicSysPsvBlock"} == "frontend_detail_index_tabs_cross_selling" && {config name="CseEightselectBasicSysPsvPosition"}=="widget_after"}
            {include file="frontend/detail/custom/cse.tpl"}
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_detail_tabs_navigation_inner"}
    {if $isCseWidgetConfigValid}
        {if {config name="CseEightselectBasicSysPsvPosition"} == "widget_after"}
            {$smarty.block.parent}
        {/if}

        {if {config name="CseEightselectBasicSysPsvBlock"} == "frontend_detail_tabs"}
        {block name="frontend_detail_tabs_cse"}
            {if (!{config name="CseEightselectBasicPreviewActive"} && !$smarty.get.preview) || ({config name="CseEightselectBasicPreviewActive"}  && $smarty.get.preview)}
                <a href="#" class="tab--link" title="Dazu passt" data-tabName="cse" style="display: none;">Dazu passt</a>
            {/if}
        {/block}
        {/if}

        {if {config name="CseEightselectBasicSysPsvPosition"} == "widget_before"}
            {$smarty.block.parent}
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="frontend_detail_tabs_content_inner"}
    {if $isCseWidgetConfigValid}
        {if {config name="CseEightselectBasicSysPsvPosition"} == "widget_after"}
            {$smarty.block.parent}
        {/if}

        {if {config name="CseEightselectBasicSysPsvBlock"} == "frontend_detail_tabs"}
            {if (!{config name="CseEightselectBasicPreviewActive"} && !$smarty.get.preview) || ({config name="CseEightselectBasicPreviewActive"}  && $smarty.get.preview)}
                {block name="frontend_detail_tabs_content_cse"}
                        <div class="tab--container -eightselect-widget-sw-tab-container" style="display: none;">
                        {block name="frontend_detail_tabs_content_cse_inner"}
                            {block name="frontend_detail_tabs_content_cse_title"}
                                    <div class="tab--header">
                                    {block name="frontend_detail_tabs_content_cse_title_inner"}
                                            <a href="#" class="tab--title" title="Dazu passt">Dazu passt</a>
                                    {/block}
                                    </div>
                            {/block}

                            {block name="frontend_detail_tabs_cse_preview"}
                                    <div class="tab--preview">
                                        Dazu passt
                                    </div>
                            {/block}

                            {block name="frontend_detail_tabs_content_cse_description"}
                                    <div class="tab--content">
                                    {* Offcanvas buttons *}
                                    {block name='frontend_detail_cse_buttons_offcanvas'}
                                            <div class="buttons--off-canvas">
                                        {block name='frontend_detail_cse_buttons_offcanvas_inner'}
                                                <a href="#" title="{"{s name="OffcanvasCloseMenu" namespace="frontend/detail/description"}{/s}"|escape}" class="close--off-canvas">
                                            <i class="icon--arrow-left"></i>
                                            {s name="OffcanvasCloseMenu" namespace="frontend/detail/description"}{/s}
                                            </a>
                                        {/block}
                                        </div>
                                    {/block}
                                    {include file="frontend/detail/custom/cse.tpl"}
                                    </div>
                            {/block}

                        {/block}
                        </div>
                {/block}
            {/if}
        {/if}

        {if {config name="CseEightselectBasicSysPsvPosition"} == "widget_before"}
            {$smarty.block.parent}
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}

{/block}
