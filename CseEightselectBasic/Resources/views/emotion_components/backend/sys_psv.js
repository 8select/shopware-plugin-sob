//{block name="emotion_components/backend/sys_psv"}
Ext.define('Shopware.apps.Emotion.view.components.SysPsv', {

    extend: 'Shopware.apps.Emotion.view.components.Base',

    alias: 'widget.emotion-8select-syspsv-element',

    snippets: {
        addProductLabel: '{s name=emotion/syspsv/ordernumber}Product Ordernumber{/s}'
    },

    initComponent: function() {
        var me = this;

        me.callParent(arguments);
        me.elementFieldset.add(me.createCustomFields());

        me.setInitialValue();
    },

    createCustomFields: function() {
        var me = this;

        return [
            Ext.create('Shopware.form.field.ArticleSearch', {
                name: 'article_search_field',
                fieldLabel: me.snippets.addProductLabel,
                returnValue: 'number',
                hiddenReturnValue: 'number',
                width: 400,
                formFieldConfig: {
                    labelWidth: 180,
                    width: 400
                },
                articleStore: Ext.create('Shopware.store.Article'),
                listeners: {
                    valueselect: function(field, name, number, record) {
                        if (record instanceof Ext.data.Model) {
                            me.setFieldValue('sys_psv_ordernumber', record);
                        }
                    }
                }
            })
        ];
    },

    setFieldValue: function(fieldName, record) {
        var me = this;

        me.setValue(fieldName, record.get('number'));
    },

    setValue: function(fieldName, value) {
        var me = this,
            items = me.elementFieldset.items.items;

        Ext.each(items, function(item) {
            if(item.name === fieldName) {
                if(typeof item.items !== 'undefined') {
                    Ext.each(item.items.items, function(subitem) {
                        if(typeof subitem !== 'undefined') {
                            if (typeof item.searchFieldName !== 'undefined' && subitem.name === item.searchFieldName) {
                                subitem.setValue(value);
                            }
                        }
                    });
                } else {
                    item.setValue(value);
                }
            }
        });
    },

    setInitialValue: function() {
        var me = this,
            items = me.elementFieldset.items.items;

        Ext.each(items, function(item) {
            if(item.name === 'sys_psv_ordernumber') {
                me.setValue('article_search_field', item.getValue());
            }
        });
    }

});
//{/block}