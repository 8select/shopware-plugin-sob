{extends file="parent:frontend/detail/index.tpl"}

{if {config name="8s_selected_detail_block"} != "none"}

    {block name='frontend_detail_index_header'}
        {if {config name="8s_selected_detail_block"} == "frontend_detail_index_header" && {config name="8s_widget_placement"}=="widget_before"}
            {include file="frontend/custom/cse.tpl"}
        {/if}
        {$smarty.block.parent}
        {if {config name="8s_selected_detail_block"} == "frontend_detail_index_header" && {config name="8s_widget_placement"}=="widget_after"}
            {include file="frontend/custom/cse.tpl"}
        {/if}
    {/block}

    {block name="frontend_detail_index_detail"}
        {if {config name="8s_selected_detail_block"} == "frontend_detail_index_detail" && {config name="8s_widget_placement"}=="widget_before"}
            {include file="frontend/custom/cse.tpl"}
        {/if}
        {$smarty.block.parent}
        {if {config name="8s_selected_detail_block"} == "frontend_detail_index_detail" && {config name="8s_widget_placement"}=="widget_after"}
            {include file="frontend/custom/cse.tpl"}
        {/if}
    {/block}

    {* Crossselling tab panel *}
    {block name="frontend_detail_index_tabs_cross_selling"}
        {if {config name="8s_selected_detail_block"} == "frontend_detail_index_tabs_cross_selling" && {config name="8s_widget_placement"}=="widget_before"}
            {include file="frontend/custom/cse.tpl"}
        {/if}
        {$smarty.block.parent}
        {if {config name="8s_selected_detail_block"} == "frontend_detail_index_tabs_cross_selling" && {config name="8s_widget_placement"}=="widget_after"}
            {include file="frontend/custom/cse.tpl"}
        {/if}
    {/block}


    {/if}
