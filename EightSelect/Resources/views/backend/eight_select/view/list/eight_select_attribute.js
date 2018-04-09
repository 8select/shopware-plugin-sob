Ext.define('Shopware.apps.EightSelect.view.list.EightSelectAttribute', {
    extend: 'Shopware.grid.Panel',
    alias:  'widget.8select-attributes-grid',
    region: 'center',

    configure: function() {
        return {
            actionColumn: false,
            rowEditing: true,
            columns: {
                eightSelectAttribute: {
                    header: '8Select Attribute',
                    editable: false
                },
                shopwareAttributeName: {
                    header: 'Shopware Attribute Name',
                    editor: {
                        xtype: 'combobox',
                        allowBlank: false,
                        valueField: 'id',
                        displayField: 'description',
                        store : '',
                        editable: false
                    }
                }
            }
        };
    }
});
