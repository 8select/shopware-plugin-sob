Ext.define('Shopware.apps.EightSelect.view.list.EightSelectAttribute', {
    extend: 'Shopware.grid.Panel',
    alias:  'widget.8select-attributes-grid',
    region: 'center',

    configure: function() {
        return {
            actionColumn: false,
            addButton: false,
            deleteButton: false,
            rowEditing: true,
            pagingbar: false,
            columns: {
                eightSelectAttribute: {
                    header: '8select Attribute',
                    editor: {
                        editable: false
                    }
                },
                shopwareAttribute: {
                    header: 'Shopware Attribute',
                    editor: {
                        xtype: 'combobox',
                        allowBlank: false,
                        valueField: 'column_name',
                        displayField: 'label',
                        store : Ext.create('Shopware.apps.EightSelect.store.ShopwareAttribute'),
                        editable: false
                    }
                }
            }
        };
    }
});
