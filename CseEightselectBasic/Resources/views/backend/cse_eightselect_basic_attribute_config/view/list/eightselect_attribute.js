Ext.define("Shopware.apps.CseEightselectBasicAttributeConfig.view.list.EightselectAttribute",{
    extend: "Shopware.grid.Panel",
    alias: "widget.8select-attributes-grid",
    id: "8select-attributes-grid",
    region: "center",
    listeners: {
      beforerender: function() {
        var grid = Ext.getCmp("8select-attributes-grid")

        Ext.create("Shopware.apps.CseEightselectBasicAttributeConfig.store.ShopwareAttribute", {
          storeId: 'shopware-attribute-label-store'
        }).load({
          callback: function(records, operation, success) {
            if(success) {
              grid.getView().refresh();
            }
          }
        })
      }
    },
    configure: function() {
      return {
        actionColumn: false,
        addButton: false,
        deleteButton: false,
        rowEditing: true,
        pagingbar: true,
        columns: {
          eightselectAttributeLabel: {
            header: "8select Attribute",
            width: 250,
            editor: {
              editable: false
            }
          },
          eightselectAttributeLabelDescr: {
            header: "Description",
            width: 450,
            renderer: function(value, meta){
              meta.style = 'white-space: normal;'; 
              meta.tdAttr = 'style="white-space: normal;"';
              return value;      
            },
            editor: {
              editable: false
            }
          },
          shopwareAttribute: {
            header: "Shopware Attribute",
            renderer: function(val, meta, rec, rowIdx, colIdx, store, view) {
              var labelStore = Ext.getStore('shopware-attribute-label-store')
              var currentField = store.data.items[rowIdx].data.shopwareAttribute

              if (!labelStore.data || labelStore.data.items.length === 0) {
                return "Lade Daten..."
              }
              
              var targetItem = labelStore.data.items.find(function(item) {
                  return item.data.column_name === currentField
              })

              if (!targetItem) {
                return val
              }

              return targetItem.data.label
            },
            editor: {
              xtype: "combobox",
              allowBlank: false,
              valueField: "column_name",
              displayField: "label",
              tpl: Ext.create('Ext.XTemplate',
                '<tpl for=".">',
                  '<div style="padding:5px" class="x-boundlist-item">{literal}{label}{/literal} <i style="color:#ccc">({literal}{column_name}{/literal})</i></div>',
                '</tpl>'
              ),
              listeners: {
                beforeselect: function() {
                  if (this.value.length === 0) {
                    return true
                  }
                  
                  var isArticleField = this.value.some(function(field) {
                    return field.match('s_articles')
                  })

                  if (isArticleField) {
                    return false
                  }
                  return true
                }
              },
              store: Ext.create(
                "Shopware.apps.CseEightselectBasicAttributeConfig.store.ShopwareAttribute"
              ),
              editable: false
            }
          }
        }
      }
    },
    initComponent: function() {
      var me = this;
  
      var configValidationRequest = Ext.Ajax.request({
        async: false,
        url: "{url controller=CseEightselectBasicConfigValidation action=validate}"
      });
  
      var configValidationResult = Ext.decode(configValidationRequest.responseText).validationResult;
  
      var isConfigValid = function(configValidationResult) {
        return configValidationResult.isValid;
      };
  
      var getErrorMessages = function(configValidationResult) {
        return configValidationResult.messages;
      };
  
      function enableAttributeMapping() {
        me.setDisabled(false);
      }
  
      function disableAttributeMapping() {
        me.setDisabled(true);
      }
  
      me.callParent(arguments);
      disableAttributeMapping();
  
      if (isConfigValid(configValidationResult)) {
        enableAttributeMapping();
      }
    }
});
