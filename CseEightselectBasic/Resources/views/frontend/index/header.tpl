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
        /* 8select CSE - Shopware Plugin __VERSION__ */
        (function(d, s, w) {
            function getUrlParameter(name) {
                try {
                    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                    var results = regex.exec(location.search);
                    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
                } catch (error) {
                    return '';
                }
            }
            window.eightlytics || function (w) {
                w.eightlytics = function () {
                    window.eightlytics.queue = window.eightlytics.queue || [];
                    window.eightlytics.queue.push(arguments);
                };
            }(w);
            var script = d.createElement(s);
            script.src = 'https://__SUBDOMAIN__.8select.io/{config name="8s_merchant_id"}/loader.js';

            if (!!getUrlParameter('8s_demo')) {
                script.src = 'https://__SUBDOMAIN__.8select.io/db54750f-80fc-4818-9455-30ca233225dc/loader.js';
            }

            var entry = d.getElementsByTagName(s)[0];
            entry.parentNode.insertBefore(script, entry);
        })(document, 'script', window);
    </script>

    <script type="text/javascript">
        if (typeof _eightselect_shop_plugin === "undefined") {
            var _eightselect_shop_plugin = {};
        }

        _eightselect_shop_plugin.showSys = function () {
            return;
        };
        _eightselect_shop_plugin.hideSys = function () {
            return;
        };

        _eightselect_shop_plugin.addToCart = function (sku, quantity, Promise) {
            try {
                var eightselectCartForm = document.querySelector('#eightselect_cart_trigger_form');
                eightselectCartForm.querySelector('#eightselect_cart_trigger_form_sku').setAttribute('name', 'sAdd');
                eightselectCartForm.querySelector('#eightselect_cart_trigger_form_quantity').setAttribute('name', 'sQuantity');
                eightselectCartForm.querySelector('#eightselect_cart_trigger_form_sku').value = sku;
                eightselectCartForm.querySelector('#eightselect_cart_trigger_form_quantity').value = quantity;

                $('#eightselect_cart_trigger_form').swAddArticle();

                eightselectCartForm.querySelector('#eightselect_cart_trigger_form_submit').click();

                eightselectCartForm.querySelector('#eightselect_cart_trigger_form_sku').setAttribute('name', '');
                eightselectCartForm.querySelector('#eightselect_cart_trigger_form_quantity').setAttribute('name', '');

                return Promise.resolve();
            } catch (error) {
                console.log('8select add2cart logic failed');
                console.log(error);
                return Promise.reject(error);
            }
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
                if (window.document.readyState === 'loading') {
                    window.addEventListener('DOMContentLoaded', _eightselect_shop_plugin.showSys);
                    return;
                }

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
                _eightselect_shop_plugin.hideSys();
            };

            if (window.document.readyState !== 'loading') {
                domListener();
            } else {
                window.addEventListener('DOMContentLoaded', domListener);
            }
        </script>
    {/if}
{/block}
