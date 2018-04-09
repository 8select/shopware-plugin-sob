Ext.define('Shopware.apps.EightSelect.view.list.Attribute', {
    extend: 'Shopware.grid.Panel',
    alias:  'widget.8select-attributes-grid',
    region: 'center',

    configure: function() {
        return {
            actionColumn: false,
            rowEditing: true,
            columns: {
                eightSelectAttribute: {
                    header: 'eightSelectAttribute',
                    editable: false
                },
                shopwareAttibuteName: {
                    header: 'shopwareAttibuteName',
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
