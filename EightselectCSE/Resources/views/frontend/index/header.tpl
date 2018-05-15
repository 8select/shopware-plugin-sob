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
    {if !$checkoutFinish}
        <script type="text/javascript">
            if (typeof _eightselect_config === "undefined") {
                var _eightselect_config = {};
            }
            _eightselect_config.sys = _eightselect_config.sys || {};
            _eightselect_config.sys.callback = function (error) {
                if (error) {
                    eightselect_shopware.hideSys();
                } else {
                    eightselect_shopware.showSys();
                }
            }
        </script>
        <script async src="https://wgt.8select.io/{config name="8s_merchant_id"}/loader.js"></script>

        <script type="text/javascript">
            if (typeof eightselect_shopware === "undefined") {
                var eightselect_shopware = {};
            }
            eightselect_shopware.addToCart = function (sku) {
                document.getElementById('eightselect_cart_trigger_form_sku').value = sku;
                document.getElementById('eightselect_cart_trigger_form_submit').click();
            };

            eightselect_shopware.showSys = function () {
                return;
            };
            eightselect_shopware.hideSys = function () {
                return;
            };
        </script>
    {/if}

    {if {config name="8s_selected_detail_block"} == "frontend_detail_tabs"}
        {* Activate description tab - SYS tab will be activated when CSE finds a set *}
        {if {config name="8s_widget_placement"} == "widget_before"}
            <script type="text/javascript">

                eightselect_shopware.hideSys = function () {
                    var navNodes = document.querySelectorAll('.tab-menu--product .tab--navigation .tab--link');
                    if (!navNodes) {
                        return;
                    }
                    var contentNodes = document.querySelectorAll('.tab-menu--product .tab--container-list .tab--container');
                    if (!contentNodes) {
                        return;
                    }

                    navNodes[1].className += " " + "is--active";
                    contentNodes[1].className += " " + "is--active";

                    navNodes[0].className = navNodes[0].className.replace('is--active', '');
                    contentNodes[0].className = contentNodes[0].className.replace('is--active', '');
                    navNodes[0].style.display = "none";
                    contentNodes[0].style.display = "none";
                };

                eightselect_shopware.showSys = function () {
                    var navNodes = document.querySelectorAll('.tab-menu--product .tab--navigation .tab--link');
                    if (!navNodes) {
                        return;
                    }
                    var contentNodes = document.querySelectorAll('.tab-menu--product .tab--container-list .tab--container');
                    if (!contentNodes) {
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
                    eightselect_shopware.hideSys();
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

                eightselect_shopware.hideSys = function () {
                    var navNodes = document.querySelectorAll('.tab-menu--product .tab--navigation .tab--link');
                    if (!navNodes) {
                        return;
                    }
                    var navPosition = navNodes.length - 1;

                    var contentNodes = document.querySelectorAll('.tab-menu--product .tab--container-list .tab--container');
                    if (!contentNodes) {
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

                eightselect_shopware.showSys = function () {
                    var navNodes = document.querySelectorAll('.tab-menu--product .tab--navigation .tab--link');
                    if (!navNodes) {
                        return;
                    }
                    var navPosition = navNodes.length - 1;

                    var contentNodes = document.querySelectorAll('.tab-menu--product .tab--container-list .tab--container');
                    if (!contentNodes) {
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
