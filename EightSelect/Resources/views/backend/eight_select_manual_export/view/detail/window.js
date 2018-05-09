Ext.define('Shopware.apps.EightSelectManualExport.view.detail.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.8select-export-window',
    height: 450,
    title : '{s name=window_title}Manual Export{/s}',
    layout: 'border',

    initComponent: function() {
        var me = this;

        me.items = [
            Ext.create('Shopware.apps.EightSelectManualExport.view.detail.Export')
        ];

        me.callParent(arguments);
    }


});
