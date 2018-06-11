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
      var htmlContainerCheck = Ext.Ajax.request({ async: false, url: '{url controller=CseEightselectBasicManualExport action=checkForHtmlContainer}' })
      var sysAccCheck = Ext.Ajax.request({ async: false, url: '{url controller=CseEightselectBasicManualExport action=checkForSysAcc}' })
      var previewCheck = Ext.Ajax.request({ async: false, url: '{url controller=CseEightselectBasicManualExport action=checkForPreviewMode}' })

      var active = Ext.decode(stateCheck.responseText).active
      var apiId = Ext.decode(apiCheck.responseText).apiId
      var feedId = Ext.decode(feedCheck.responseText).feedId
      var htmlContainer = Ext.decode(htmlContainerCheck.responseText).container
      var sysAcc = Ext.decode(sysAccCheck.responseText).sysAcc
      var previewMode = Ext.decode(previewCheck.responseText).previewMode

      var CALL_EIGHTSELECT = "Überprüfen Sie bitte Ihre Plugin-Einstellungen oder wenden Sie sich an 8select."
      var EMPTY_API_ID_MESSAGE = "Keine API ID hinterlegt. " + CALL_EIGHTSELECT;
      var INVALID_API_ID_MESSAGE = "API ID ungültig. " + CALL_EIGHTSELECT;
      var EMPTY_FEED_ID_MESSAGE = "Keine Feed ID hinterlegt. " + CALL_EIGHTSELECT;
      var INVALID_FEED_ID_MESSAGE = "Feed ID ungültig. " + CALL_EIGHTSELECT;
      var EMPTY_HTML_CONTAINER_MESSAGE = "Kein Widget-Platzhalter im HTML-Container. " + CALL_EIGHTSELECT;
      var INVALID_HTML_CONTAINER_MESSAGE = "Widget-Platzhalter im HTML-Container ungültig. " + CALL_EIGHTSELECT;
      var PLUGIN_NOT_ACTIVE = "Plugin ist nicht aktiv. " + CALL_EIGHTSELECT;
      var SYS_ACC_IS_EMPTY = "Keine Einstellung für SYS-ACC Widget hinterlegt. " + CALL_EIGHTSELECT;
      var PREVIEW_MODE_IS_EMPTY = "Keine Einstellung für Vorschau-Modus hinterlegt. " + CALL_EIGHTSELECT;

      function enableAttributeMapping() {
        me.setDisabled(false)
      }

      function disableAttributeMapping() {
        me.setDisabled(true)
      }

      function checkForActiveState(validateState) {
        if (!validateState || validateState === null) {
            Shopware.Notification.createGrowlMessage("", PLUGIN_NOT_ACTIVE);
        }
    }

    function checkForApiId(id) {
        if (!id || id === null ||id.length === 0) {
            Shopware.Notification.createGrowlMessage("", EMPTY_API_ID_MESSAGE);
        }

        if (id && id !== null && id.length !== 36) {
            Shopware.Notification.createGrowlMessage("", INVALID_API_ID_MESSAGE);
        }
    }

    function checkForFeedId(id) {
        if (!id || id === null ||id.length === 0) {
            Shopware.Notification.createGrowlMessage("", EMPTY_FEED_ID_MESSAGE);
        }

        if (id && id !== null && id.length !== 36) {
            Shopware.Notification.createGrowlMessage("", INVALID_FEED_ID_MESSAGE);
        }
    }

    function checkForHtmlContainer(container) {
        if (!container || container === null ||container.length === 0) {
            Shopware.Notification.createGrowlMessage("", EMPTY_HTML_CONTAINER_MESSAGE);
        }

        if (container && container !== null && container !== "CSE_SYS") {
            Shopware.Notification.createGrowlMessage("", INVALID_HTML_CONTAINER_MESSAGE);
        }
    }

    function checkForSysAccAction(option) {
        if (option === null) {
            Shopware.Notification.createGrowlMessage("", SYS_ACC_IS_EMPTY);
        }
    }

    function checkForPreviewMode(mode) {
        if (mode === null) {
            Shopware.Notification.createGrowlMessage("", PREVIEW_MODE_IS_EMPTY);
        }
    }

    function validatePluginConfig(callback) {

        checkForActiveState(active)
        checkForApiId(apiId)
        checkForFeedId(feedId)
        checkForHtmlContainer(htmlContainer)
        checkForSysAccAction(sysAcc)
        checkForPreviewMode(previewMode)

        var everythingSet =     active !== null && 
                                apiId !== null && 
                                feedId !== null && 
                                htmlContainer !== null && 
                                sysAcc !== null && 
                                previewMode !== null;

        var everythingValid =   active && 
                                apiId.length === 36 && 
                                feedId.length === 36 &&
                                htmlContainer === "CSE_SYS";

        if (everythingSet) {
            if (everythingValid) {
                callback();
            }
        }
    }

      me.callParent(arguments);
      disableAttributeMapping()
      validatePluginConfig(enableAttributeMapping)
    }
  }
);
