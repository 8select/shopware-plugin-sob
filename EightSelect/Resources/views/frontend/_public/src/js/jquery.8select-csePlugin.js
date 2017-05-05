;(function ($, window, StateManager) {
    'use strict';
    $.plugin('8selectCsePlugin', {
        defaults: {
            defaultValue: 'default'
        },
        init: function () {
            console.log('INIT 8SELECT CSE PLUGIN');
            var me = this;
            me.applyDataAttributes();
        },
        destroy: function() {
            var me = this;
            me._destroy();
        }
    });
})(jQuery, window, StateManager);
