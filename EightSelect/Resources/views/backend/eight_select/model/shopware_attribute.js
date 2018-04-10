Ext.define('Shopware.apps.EightSelect.model.ShopwareAttribute', {

    extend: 'Ext.data.Model',

    fields: [
        { name : 'column_name', type: 'string' },
        { name : 'label', type: 'string' },

    ],

    proxy: {
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         * @object
         */
        api: {
            read: '{url controller=EightSelect action=getArticleAttributes}'
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

