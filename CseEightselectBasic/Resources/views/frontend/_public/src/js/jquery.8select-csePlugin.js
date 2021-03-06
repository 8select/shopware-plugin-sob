;(function ($, window, StateManager) {
    'use strict';
    $.plugin('8selectCsePlugin', {
        defaults: {
            skuSelector: '[itemprop="sku"]'
        },
        init: function () {
            var me = this;
            me.applyDataAttributes();
            me.currentSku = $(me.defaults.skuSelector).text();
            me.registerEventListeners();
        },
        registerEventListeners: function() {
            var me = this;
            $.subscribe('plugin/swAjaxVariant/onRequestData', $.proxy(me.onRequestData, me));
            $.subscribe('plugin/swEmotionLoader/onLoadEmotionFinished', $.proxy(me.onLoadEmotionFinished, me));
        },
        onRequestData: function() {
            var me = this; 
            var newSku = $(me.defaults.skuSelector).text();
            if (me.currentSku !== newSku && typeof _8select !== "undefined" && _8select.reinitSys) {
                try {
                    _8select.reinitSys(newSku, me.currentSku);
                } catch (error) {
                    console.error(error)
                }
            }
            me.currentSku = newSku;
        },
        onLoadEmotionFinished: function() {
            if (typeof _8select !== "undefined" && _8select.initCSE) {
                try {
                    _8select.initCSE();
                } catch (error) {
                    console.error(error)
                }
            }
        },
        destroy: function() {
            var me = this;
            me._destroy();
        }
    });

    StateManager.addPlugin('.product--details', '8selectCsePlugin');
    StateManager.addPlugin('.content--emotions', '8selectCsePlugin');

})(jQuery, window, StateManager);
