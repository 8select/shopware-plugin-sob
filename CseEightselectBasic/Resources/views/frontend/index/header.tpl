{extends file="parent:frontend/index/header.tpl"}

{block name="frontend_index_header_css_screen"}
    {$smarty.block.parent}
    {if {config name="8s_custom_css"} != ""}
    <style>
        {include file='string:{config name="8s_custom_css"}'}
    </style>
    {/if}
{/block}

{block name='frontend_index_header_javascript_tracking'}
    {$smarty.block.parent}
    <script type="text/javascript">
        if (typeof _eightselect_config === "undefined") {
            var _eightselect_config = {};
        }
        _eightselect_config.sys = _eightselect_config.sys || {};
        _eightselect_config.sys.callback = function (error) {
            if (error) {
                _eightselect_shop_plugin.hideSys();
            } else {
                _eightselect_shop_plugin.showSys();
            }
        }
    </script>

    <script type="text/javascript">
        (function(d, s, w) {
            window.eightlytics || function (w) {
                w.eightlytics = function () {
                    window.eightlytics.queue = window.eightlytics.queue || [];
                    window.eightlytics.queue.push(arguments);
                };
            }(w);
            var script = d.createElement(s);
            script.src = 'https://__SUBDOMAIN__.8select.io/{config name="8s_merchant_id"}/loader.js';
            var entry = d.getElementsByTagName(s)[0];
            entry.parentNode.insertBefore(script, entry);
        })(document, 'script', window);
    </script>

    <script type="text/javascript">
        if (typeof _eightselect_shop_plugin === "undefined") {
            var _eightselect_shop_plugin = {};
        }
        _eightselect_shop_plugin.addToCart = function (sku, quantity, Promise) {
            document.getElementById('eightselect_cart_trigger_form_sku').value = sku;
            document.getElementById('eightselect_cart_trigger_form_quantity').value = quantity;
            document.getElementById('eightselect_cart_trigger_form_submit').click();

            return Promise.resolve();
        };

        _eightselect_shop_plugin.showSys = function () {
            return;
        };
        _eightselect_shop_plugin.hideSys = function () {
            return;
        };
    </script>

    {if {config name="8s_selected_detail_block"} == "frontend_detail_tabs"}
        {* Activate description tab - SYS tab will be activated when CSE finds a set *}
        <script type="text/javascript">
            _eightselect_shop_plugin.hideSys = function () {
                var tabs = document.querySelectorAll('.tab-menu--product .tab--navigation .tab--link');

                {if {config name="8s_widget_placement"} == "widget_before"}
                    var tabToActivate = tabs && tabs[1];
                {/if}

                {if {config name="8s_widget_placement"} == "widget_after"}
                    var tabToActivate = tabs && tabs[0];
                {/if}

                if (tabToActivate) {
                    tabToActivate.click();
                }
                var cseTab = document.querySelector('a[data-tabname=cse]');
                var cseContainer = document.querySelector('div.-eightselect-widget-sw-tab-container');

                if (cseTab && cseTab.style.display !== 'none') {
                    cseTab.style.display = 'none';
                }
                if (cseContainer && cseContainer.style.display !== 'none') {
                    cseContainer.style.display = 'none';
                }
            };

            _eightselect_shop_plugin.showSys = function () {
                var cseTab = document.querySelector('a[data-tabname=cse]');
                var cseContainer = document.querySelector('div.-eightselect-widget-sw-tab-container');

                if (!cseTab || !cseContainer) {
                    return;
                }

                cseTab.style.display = '';
                cseContainer.style.display = '';
                cseTab.click();
            };

            var domListener = function () {
                window.removeEventListener('DOMContentLoaded', domListener);
            };

            if (window.document.readyState !== 'loading') {
                domListener();
            } else {
                window.addEventListener('DOMContentLoaded', domListener);
            }
        </script>
    {/if}
{/block}
