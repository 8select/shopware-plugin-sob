Ext.define('Shopware.apps.EightSelect.model.Attribute', {

    extend: 'Ext.data.Model',

    fields: [
        { name : 'eightSelectAttribute', type: 'string' },
        { name : 'shopwareAttibuteName', type: 'string' },
        { name : 'shopwareAttibute', type: 'string' },

    ],

    proxy: {
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            read: '{url controller=EightSelect action=getAttributeList}'
        },
        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data'
        }
    }
});

