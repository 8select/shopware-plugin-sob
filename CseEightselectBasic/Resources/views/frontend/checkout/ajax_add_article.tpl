{extends file='parent:frontend/checkout/ajax_add_article.tpl'}

{block name='checkout_ajax_add_actions'}
    {$smarty.block.parent}

    {if ({config name="8s_sys_acc_enabled"} && !{config name="8s_preview_mode_enabled"})
        || ({config name="8s_sys_acc_enabled"} && {config name="8s_preview_mode_enabled"} && {$smarty.get.preview})}
        
        <div class="modal--title">Dazu passt:</div>
        <div class="modal--article block-group">

            <div data-sku="{$sArticle.ordernumber}" data-include-css="true" data-8select-widget-id="sys-acc"></div>
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
