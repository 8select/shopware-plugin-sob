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
              valueField: "column_name",
              displayField: "label",
              store: Ext.create(
                "Shopware.apps.CseEightselectBasicAttributeConfig.store.ShopwareAttribute"
              ),
              editable: false
            }
          }
        }
      };
    },

    initComponent: function () {
      var me = this

      var stateCheck = Ext.Ajax.request({ async: false, url: '{url controller=CseEightselectBasicManualExport action=checkForActiveState}' })
      var apiCheck = Ext.Ajax.request({ async: false, url: '{url controller=CseEightselectBasicManualExport action=checkForApiId}' })
      var feedCheck = Ext.Ajax.request({ async: false, url: '{url controller=CseEightselectBasicManualExport action=checkForFeedId}' })
        
      var active = Ext.decode(stateCheck.responseText).active
      var apiId = Ext.decode(apiCheck.responseText).apiId
      var feedId = Ext.decode(feedCheck.responseText).feedId

      var EMPTY_API_ID_MESSAGE = "Keine API ID hinterlegt. Bitte überprüfen Sie Ihre Plugin-Einstellungen oder wenden Sie sich an 8select."
      var INVALID_API_ID_MESSAGE = "API ID ungültig. Bitte überprüfen Sie Ihre Plugin-Einstellungen oder wenden Sie sich an 8select."

      var EMPTY_FEED_ID_MESSAGE = "Keine Feed ID hinterlegt. Bitte überprüfen Sie Ihre Plugin-Einstellungen oder wenden Sie sich an 8select."
      var INVALID_FEED_ID_MESSAGE = "Feed ID ungültig. Bitte überprüfen Sie Ihre Plugin-Einstellungen oder wenden Sie sich an 8select."

      var PLUGIN_NOT_ACTIVE = "Plugin ist nicht aktiv. Bitte überprüfen Sie Ihre Plugin-Einstellungen oder wenden Sie sich an 8select."

      function enableAttributeMapping() {
        me.setDisabled(false)
      }

      function disableAttributeMapping() {
        me.setDisabled(true)
      }

      function checkForActiveState(validateState) {
        if (!validateState || validateState === null) {
          Shopware.Notification.createGrowlMessage("", PLUGIN_NOT_ACTIVE)
        }
      }

      function checkForApiId(id) {
        if (!id || id === null ||id.length === 0) {
          Shopware.Notification.createGrowlMessage("", EMPTY_API_ID_MESSAGE)
        }

        if (id && id !== null && id.length !== 36) {
          Shopware.Notification.createGrowlMessage("", INVALID_API_ID_MESSAGE)
        }
      }

      function checkForFeedId(id) {
        if (!id || id === null ||id.length === 0) {
          Shopware.Notification.createGrowlMessage("", EMPTY_FEED_ID_MESSAGE)
        }

        if (id && id !== null && id.length !== 36) {
          Shopware.Notification.createGrowlMessage("", INVALID_FEED_ID_MESSAGE)
        }
      }

      function validatePluginConfig(callback) {

          checkForActiveState(active)
          checkForApiId(apiId)
          checkForFeedId(feedId)

          if (active !== null && apiId !== null && feedId !== null) {
            if (active && apiId.length === 36 && feedId.length === 36) {
              callback()
            }
          }
      }

      me.callParent(arguments);
      disableAttributeMapping()
      validatePluginConfig(enableAttributeMapping)
    }
  }
);
