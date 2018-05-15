Ext.define('Shopware.apps.EightselectCSEManualExport', {
    extend: 'Enlight.app.SubApplication',

    name: 'Shopware.apps.EightselectCSEManualExport',

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
