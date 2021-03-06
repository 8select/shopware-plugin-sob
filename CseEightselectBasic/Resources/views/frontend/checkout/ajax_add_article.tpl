{extends file='parent:frontend/checkout/ajax_add_article.tpl'}

{block name='checkout_ajax_add_actions'}
    {$smarty.block.parent}

    {if ($isCseWidgetConfigValid && {$CseEightselectBasicSysAccActive} && !{$CseEightselectBasicPreviewActive})
        || ($isCseWidgetConfigValid && {$CseEightselectBasicSysAccActive} && {$CseEightselectBasicPreviewActive} && {$smarty.get.preview})}

        <div class="modal--article block-group -eightselect-widget-container">
            <div class="eightselect-sysacc-html" style="display: none">{include file="string:{$htmlSysAccContainer.0}"}</div>
                <div data-sku="{$sArticle.ordernumber}" data-include-css="true" data-8select-widget-id="sys-acc"></div>
            <div class="eightselect-sysacc-html" style="display: none">{include file="string:{$htmlSysAccContainer.1}"}</div>
        </div>

        <script>
            (function(){
                if (typeof _8select === "undefined") {
                    return
                }
                _8select.initCSE()
            })()
        </script>
    {/if}
{/block}


{block name='checkout_ajax_add_cross_selling'}
    {if $isCseWidgetConfigValid}
        <div class="eightselect-sw-cross-selling-container">
            {$smarty.block.parent}
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
