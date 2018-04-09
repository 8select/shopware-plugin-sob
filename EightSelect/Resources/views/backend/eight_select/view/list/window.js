Ext.define('Shopware.apps.EightSelect.view.list.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.8select-attributes-window',
    height: 450,
    title : '{s name=window_title}Attribute mapping{/s}',

    configure: function() {
        return {
            listingGrid: 'Shopware.apps.EightSelect.view.list.EightSelectAttribute',
            listingStore: 'Shopware.apps.EightSelect.store.EightSelectAttribute'
        };
    }
});
