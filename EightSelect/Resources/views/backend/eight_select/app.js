Ext.define('Shopware.apps.EightSelect', {
    extend: 'Enlight.app.SubApplication',

    name:'Shopware.apps.EightSelect',

    loadPath: '{url action=load}',
    bulkLoad: true,

    controllers: [ 'Main' ],

    views: [
        'list.Window',
        'list.Attribute',
    ],

    models: [ 'Attribute' ],
    stores: [ 'Attribute' ],

    launch: function() {
        return this.getController('Main').mainWindow;
    }
});
