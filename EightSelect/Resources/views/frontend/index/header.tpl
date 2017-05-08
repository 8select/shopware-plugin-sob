{extends file="parent:frontend/index/header.tpl"}

{block name="frontend_index_header_css_screen"}
    {$smarty.block.parent}
    {* todo custom css *}
    {if {config name="custom_css"} != ""}
    <style>
        {include file="string:{config name="custom_css"}"}
    </style>
    {/if}
{/block}

{block name='frontend_index_header_javascript_tracking'}
    {$smarty.block.parent}
    <script type="text/javascript">/*{literal}<![CDATA[*/
        (function(d, s) {
            var script = d.createElement(s);
            script.type = 'text/javascript';
            script.async = true;
            script.src   = {/literal}'//widget.{config name="merchant_id"}.8select.io/loader.js'{literal};
            var entry = d.getElementsByTagName(s)[0];
            entry.parentNode.insertBefore(script, entry);
        })(document, 'script');
        /*]]>{/literal}*/</script>

    {if $checkoutFinish}
        <script type="text/javascript">/*{literal}<![CDATA[*/
            window.eightlytics || function (a) {
                a.eightlytics = function () {
                    (a.eightlytics_queue = []).push(arguments)
                };
                (function (b, a, d) {
                    var c = b.createElement(a);
                    c.type = "text/javascript";
                    c.async = !0;
                    c.src = d;
                    b = b.getElementsByTagName(a)[0];
                    b.parentNode.insertBefore(c, b)
                })(a.document, "script", {/literal}"https://widget.{config name="merchant_id"}.8select.io/eightlytics/eightlytics-queue.js"{literal})
            }(window);
            /*]]>{/literal}*/</script>
            {debug}
    {/if}

{/block}
