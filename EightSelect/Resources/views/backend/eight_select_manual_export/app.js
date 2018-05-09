Ext.define('Shopware.apps.EightSelectManualExport', {
    extend: 'Enlight.app.SubApplication',

    name: 'Shopware.apps.EightSelectManualExport',

    loadPath: '{url action=load}',
    bulkLoad: true,

    controllers: ['Main'],

    views: [
        'detail.Window',
        'detail.Export',
    ],

    launch: function () {
        return this.getController('Main').mainWindow;
    }
});
