Ext.define("Shopware.apps.CseEightselectBasicAttributeConfig.view.list.EightselectAttribute", {
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
          editor: {
            xtype: "combobox",
            allowBlank: false,
            valueField: "column_name",
            displayField: "label",
            store: Ext.create("Shopware.apps.CseEightselectBasicAttributeConfig.store.ShopwareAttribute"),
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
