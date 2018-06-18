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
        var apiId = '{config name="8s_merchant_id"}';

        window.eightlytics || function (w) {
            w.eightlytics = function () {
                window.eightlytics.queue = window.eightlytics.queue || []
                window.eightlytics.queue.push(arguments)
            };
        }(w);
        var script = d.createElement(s);
        script.src   = 'https://__SUBDOMAIN__.8select.io/' + apiId + '/loader.js';
        var entry = d.getElementsByTagName(s)[0];
        entry.parentNode.insertBefore(script, entry);
        })(document, 'script', window);
    </script>

    <script async src="https://__SUBDOMAIN__.8select.io/{config name="8s_merchant_id"}/loader.js"></script>

    <script type="text/javascript">
        if (typeof _eightselect_shop_plugin === "undefined") {
            var _eightselect_shop_plugin = {};
        }
        _eightselect_shop_plugin.addToCart = function (sku) {
            document.getElementById('eightselect_cart_trigger_form_sku').value = sku;
            document.getElementById('eightselect_cart_trigger_form_submit').click();
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
        {if {config name="8s_widget_placement"} == "widget_before"}
            <script type="text/javascript">

                _eightselect_shop_plugin.hideSys = function () {
                    var navNodes = document.querySelectorAll('.tab-menu--product .tab--navigation .tab--link');
                    if (!navNodes || navNodes.length === 0) {
                        return;
                    }
                    var contentNodes = document.querySelectorAll('.tab-menu--product .tab--container-list .tab--container');
                    if (!contentNodes || contentNodes.length === 0) {
                        return;
                    }

                    navNodes[1].className += " " + "is--active";
                    contentNodes[1].className += " " + "is--active";

                    navNodes[0].className = navNodes[0].className.replace('is--active', '');
                    contentNodes[0].className = contentNodes[0].className.replace('is--active', '');
                    navNodes[0].style.display = "none";
                    contentNodes[0].style.display = "none";
                };

                _eightselect_shop_plugin.showSys = function () {
                    var navNodes = document.querySelectorAll('.tab-menu--product .tab--navigation .tab--link');
                    if (!navNodes || navNodes.length === 0) {
                        return;
                    }
                    var contentNodes = document.querySelectorAll('.tab-menu--product .tab--container-list .tab--container');
                    if (!contentNodes || contentNodes.length === 0) {
                        return;
                    }

                    navNodes[0].className += " " + "is--active";
                    contentNodes[0].className += " " + "is--active";
                    navNodes[0].style.display = "";
                    contentNodes[0].style.display = "";

                    navNodes[1].className = navNodes[1].className.replace('is--active', '');
                    contentNodes[1].className = contentNodes[1].className.replace('is--active', '');
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
        {if {config name="8s_widget_placement"} == "widget_after"}
            <script type="text/javascript">

                _eightselect_shop_plugin.hideSys = function () {
                    var navNodes = document.querySelectorAll('.tab-menu--product .tab--navigation .tab--link');
                    if (!navNodes || navNodes.length === 0) {
                        return;
                    }
                    var navPosition = navNodes.length - 1;

                    var contentNodes = document.querySelectorAll('.tab-menu--product .tab--container-list .tab--container');
                    if (!contentNodes || contentNodes.length === 0) {
                        return;
                    }
                    var contentPosition = contentNodes.length - 1;

                    navNodes[0].className += " " + "is--active";
                    contentNodes[0].className += " " + "is--active";

                    navNodes[navPosition].className = navNodes[navPosition].className.replace('is--active', '');
                    contentNodes[contentPosition].className = contentNodes[contentPosition].className.replace('is--active', '');

                    navNodes[navPosition].style.display = "none";
                    contentNodes[contentPosition].style.display = "none";
                };

                _eightselect_shop_plugin.showSys = function () {
                    var navNodes = document.querySelectorAll('.tab-menu--product .tab--navigation .tab--link');
                    if (!navNodes || navNodes.length === 0) {
                        return;
                    }
                    var navPosition = navNodes.length - 1;

                    var contentNodes = document.querySelectorAll('.tab-menu--product .tab--container-list .tab--container');
                    if (!contentNodes || contentNodes.length === 0) {
                        return;
                    }
                    var contentPosition = contentNodes.length - 1;

                    navNodes[navPosition].className += " " + "is--active";
                    contentNodes[contentPosition].className += " " + "is--active";
                    navNodes[navPosition].style.display = "";
                    contentNodes[contentPosition].style.display = "";

                    navNodes[0].className = navNodes[0].className.replace('is--active', '');
                    contentNodes[0].className = contentNodes[0].className.replace('is--active', '');
                };
            </script>
        {/if}
    {/if}
{/block}
