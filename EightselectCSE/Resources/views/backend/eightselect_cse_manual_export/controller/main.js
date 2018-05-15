Ext.define('Shopware.apps.EightselectCSEManualExport.controller.Main', {
    extend: 'Enlight.app.Controller',

    init: function() {
        var me = this;
        me.mainWindow = me.getView('detail.Window').create({ }).show();
    },
});
