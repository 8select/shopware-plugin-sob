{extends file="parent:frontend/index/header.tpl"}

{block name="frontend_index_header_css_screen"}
    {$smarty.block.parent}
    {if $isCseWidgetConfigValid && {config name="CseEightselectBasicCustomCss"} != ""}
        <style>
            {include file='string:{config name="CseEightselectBasicCustomCss"}'}
        </style>
    {/if}
{/block}

{block name='frontend_index_header_javascript_tracking'}
    {$smarty.block.parent}
    <script type="text/javascript">
        // 8select CSE - Shopware Plugin __VERSION__
        var _eightselect_shop_plugin = {
            version: '__VERSION__'
        };
    </script>
    {if $isCseWidgetConfigValid}
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

            _eightselect_config['sys-acc'] = _eightselect_config['sys-acc'] || {};
            _eightselect_config['sys-acc'].callback = function (error) {
                if (!error) {
                    _eightselect_shop_plugin.showSwCrossSelling();
                }
            }
        </script>

        <script type="text/javascript">
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
                script.src = 'https://__SUBDOMAIN__.8select.io/{config name="CseEightselectBasicApiId"}/loader.js';

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

            _eightselect_shop_plugin.dynamicallyInjectWidget = function(selector) {
                var customCseContainer = document.createElement('div');
                var customCseSnippet = `<div 
                    style="display:none" 
                    class="-eightselect-widget-container" 
                    data-sku={$sArticle.ordernumber} 
                    data-8select-widget-id="sys-psv">
                </div>`;
                var htmlContentBefore = `{include file="string:{$htmlContainer.0}"}`;
                var htmlContentAfter = `{include file="string:{$htmlContainer.1}"}`;
                var target;

                try {
                    target = document.querySelector(selector);
                } catch (error) {
                    return console.warn('8select CSE Plugin __VERSION__: Position is "CSS selector" but none was provided.');
                }

                if (!target) {
                    return console.warn('8select CSE Plugin __VERSION__: CSS selector "%s" does not exist!', selector);
                }

                var targetParent = target.parentNode;

                customCseContainer.insertAdjacentHTML('afterbegin', `
                    ${ htmlContentBefore }
                    ${ customCseSnippet }
                    ${ htmlContentAfter }
                `);

                {if {config name="CseEightselectBasicSysPsvPosition"} == "widget_before"}
                    targetParent.insertBefore(customCseContainer, target);
                {else}
                    targetParent.insertBefore(customCseContainer, target.nextSibling);
                {/if}

                if (typeof _8select !== "undefined" && _8select.initCSE) {
                    try {
                        _8select.initCSE();
                    } catch (error) {
                        console.error(error);
                    }
                }
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

        {if {config name="CseEightselectBasicSysPsvBlock"} == "frontend_css_selector"}
            {if !{config name="CseEightselectBasicPreviewActive"} || {$smarty.get.preview}}
            <script type="text/javascript">
                var injectWidget = function () {
                    window.removeEventListener('DOMContentLoaded', injectWidget);
                    _eightselect_shop_plugin.dynamicallyInjectWidget( '{config name="CseEightselectBasicSysPsvCssSelector"}' );
                } 
                window.addEventListener('DOMContentLoaded', injectWidget);
            </script>
            {/if}
        {/if}

        {if {config name="CseEightselectBasicSysPsvBlock"} == "frontend_detail_tabs"}
            {if !{config name="CseEightselectBasicPreviewActive"} || {$smarty.get.preview}}
                {* Activate description tab - SYS tab will be activated when CSE finds a set *}
                <script type="text/javascript">
                    _eightselect_shop_plugin.hideSys = function () {
                        var tabs = document.querySelectorAll('.tab-menu--product .tab--navigation .tab--link');
                        var tabToActivate = tabs && Array.prototype.slice
                            .call(tabs)
                            .filter(function(tab) {
                                    return tab.style.display !== 'none';
                                }
                            )[0];

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
        {/if}

        {if !{config name="CseEightselectBasicPreviewActive"} || {$smarty.get.preview}}
            <script>
                if (typeof _eightselect_shop_plugin === "undefined") {
                    var _eightselect_shop_plugin = {};
                }

                _eightselect_shop_plugin.showSwCrossSelling = function(sysAccWasCalled) {
                    var crossSellingContainer = document.querySelector('.eightselect-sw-cross-selling-container');
                    var eightselectSysAccHtml = document.querySelectorAll('.eightselect-sysacc-html');

                    for (let i = 0; i < eightselectSysAccHtml.length; i++) {
                        eightselectSysAccHtml[i].style.display = "block";
                    }

                    crossSellingContainer.setAttribute("style", "display: none");
                };
            </script>
        {/if}
    {/if}
{/block}
