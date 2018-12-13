Ext.define("Shopware.apps.CseEightselectBasicAttributeConfig.view.list.EightselectAttribute", {
  extend: "Shopware.grid.Panel",
  alias: "widget.8select-attributes-grid",
  id: "8select-attributes-grid",
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
          renderer: function(value, meta) {
            meta.style = "white-space: normal;";
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
            var labelStore = Ext.getStore("shopware-attribute-label-store");
            var allSelections = val.split(",")
            var currentField = store.data.items[rowIdx].data.shopwareAttribute;


            if (!labelStore.data || labelStore.data.items.length === 0) {
              return "Lade Daten...";
            }

            var selectedLabels = allSelections.map(function(selection){
              var targetItem = labelStore.data.items.find(function(item) {
                return item.data.column_name === selection;
              });

              if (!targetItem) {
                return val;
              }

              return targetItem.data.label;
            })

            if (val === "") {
              return "-"
            }

            return selectedLabels.join(", ")
          },
          editor: {
            xtype: "combobox",
            allowBlank: true,
            multiSelect: true,
            editable: false,
            valueField: "column_name",
            displayField: "label",
            store: Ext.create("Shopware.apps.CseEightselectBasicAttributeConfig.store.ShopwareAttribute"),
            tpl: Ext.create(
              "Ext.XTemplate",
              '<tpl for=".">',
              '<div style="padding:5px" class="x-boundlist-item">{literal}{label}{/literal} <i style="color:#ccc">({literal}{column_name}{/literal})</i></div>',
              "</tpl>"
            ),
            listeners: {
              change: function() {
                var nonPropertyFields = this.value.filter(function(field) {
                  return field.match(/s\_articles|additionaltext|weight|width|height|length|ean/);
                });

                if (nonPropertyFields.length > 1) {
                  alert("Es können nur Konfigurator-Gruppen und Eigenschaften mehrfach ausgewählt werden!");
                  this.clearValue();
                }

                return true;
              }
            }
          }
        }
      }
    };
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
      return configValidationResult.violations;
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
  },
  listeners: {
    beforerender: function() {
      var grid = Ext.getCmp("8select-attributes-grid");

      Ext.create("Shopware.apps.CseEightselectBasicAttributeConfig.store.ShopwareAttribute", {
        storeId: "shopware-attribute-label-store"
      }).load({
        callback: function(records, operation, success) {
          if (success) {
            grid.getView().refresh();
          }
        }
      });
    }
  }
});
