Ext.define(
  "Shopware.apps.CseEightselectBasicAttributeConfig.view.list.EightselectAttribute",
  {
    extend: "Shopware.grid.Panel",
    alias: "widget.8select-attributes-grid",
    region: "center",

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
            editor: {
              xtype: "combobox",
              allowBlank: false,
              multiSelect: true,
              valueField: "column_name",
              displayField: "label",
              listeners: {
                beforeselect: function() {
                  if (this.value.length > 1) {
                      var isArticleField = this.value.some(function(field) {
                        return field.match(/s\_articles|additionaltext|weight|width|height|length|ean/)
                      })

                      if (isArticleField) {
                        return false
                      }
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
      };
    }
  }
);
