{extends file="parent:frontend/detail/buy.tpl"}

{block name="frontend_detail_buy_configurator_inputs"}
    {$smarty.block.parent}
    {if $smarty.get.preview}
        <input type="hidden" name="preview" value="1" />
    {/if}
{/block}
