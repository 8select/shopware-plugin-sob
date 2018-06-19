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
    };
  },

  initComponent: function() {
    var me = this;

    var stateCheck = Ext.Ajax.request({
      async: false,
      url: "{url controller=CseEightselectBasicManualExport action=checkForActiveState}"
    });
    var apiCheck = Ext.Ajax.request({
      async: false,
      url: "{url controller=CseEightselectBasicManualExport action=checkForApiId}"
    });
    var feedCheck = Ext.Ajax.request({
      async: false,
      url: "{url controller=CseEightselectBasicManualExport action=checkForFeedId}"
    });
    var htmlContainerCheck = Ext.Ajax.request({
      async: false,
      url: "{url controller=CseEightselectBasicManualExport action=checkForHtmlContainer}"
    });
    var sysAccCheck = Ext.Ajax.request({
      async: false,
      url: "{url controller=CseEightselectBasicManualExport action=checkForSysAcc}"
    });
    var previewCheck = Ext.Ajax.request({
      async: false,
      url: "{url controller=CseEightselectBasicManualExport action=checkForPreviewMode}"
    });
    var sizesCheck = Ext.Ajax.request({
      async: false,
      url: "{url controller=CseEightselectBasicManualExport action=checkForSizeDefinitions}"
    });

    var active = Ext.decode(stateCheck.responseText).active;
    var apiId = Ext.decode(apiCheck.responseText).apiId;
    var feedId = Ext.decode(feedCheck.responseText).feedId;
    var htmlContainer = Ext.decode(htmlContainerCheck.responseText).container;
    var sysAcc = Ext.decode(sysAccCheck.responseText).sysAcc;
    var previewMode = Ext.decode(previewCheck.responseText).previewMode;
    var hasSizeDefinitions = Ext.decode(sizesCheck.responseText).sizeDefinitions;

    function enableAttributeMapping() {
      me.setDisabled(false);
    }

    function disableAttributeMapping() {
      me.setDisabled(true);
    }

    function checkForActiveState(validateState) {
      if (!validateState || validateState === null) {
        return "Plugin ist nicht aktiv";
      }
    }

    function checkForApiId(id) {
      if (!id || id === null || id.length === 0) {
        return "Keine API ID hinterlegt";
      }
      if (id && id !== null && id.length !== 36) {
        return "API ID ist ungültig";
      }
    }

    function checkForFeedId(id) {
      if (!id || id === null || id.length === 0) {
        return "Keine Feed ID hinterlegt";
      }
      if (id && id !== null && id.length !== 36) {
        return "Feed ID ist ungültig";
      }
    }

    function checkForHtmlContainer(container) {
      if (!container || container === null || container.length === 0) {
        return "Kein Widget-Platzhalter im HTML-Container";
      }
      if (container && container !== null && container !== "CSE_SYS") {
        return "Widget-Platzhalter im HTML-Container ist ungültig";
      }
    }

    function checkForSysAccAction(option) {
      if (option === null) {
        return "Keine Einstellung für SYS-ACC Widget hinterlegt";
      }
    }

    function checkForPreviewMode(mode) {
      if (mode === null) {
        return "Keine Einstellung für Vorschau-Modus hinterlegt";
      }
    }

    function checkForSizeDefinitions(hasSizes) {
      if (!hasSizes) {
        return (
          "Keine Attributgruppe als Größe definiert. Mehr Infos finden Sie in der " +
          "<a href='https://www.8select.com/8select-cse-installationsanleitung-shopware#5-konfiguration-attributfelder' target='_blank'>Installationsanleitung</a>"
        );
      }
    }

    function validationDebugInfo() {
      return [
        checkForActiveState(active),
        checkForApiId(apiId),
        checkForFeedId(feedId),
        checkForHtmlContainer(htmlContainer),
        checkForSysAccAction(sysAcc),
        checkForPreviewMode(previewMode),
        checkForSizeDefinitions(hasSizeDefinitions)
      ];
    }

    function validatePluginConfig(callback) {
      var everythingSet =
        active !== null &&
        apiId !== null &&
        feedId !== null &&
        htmlContainer !== null &&
        sysAcc !== null &&
        previewMode !== null;

      var everythingValid =
        active &&
        apiId &&
        apiId.length === 36 &&
        feedId &&
        feedId.length === 36 &&
        htmlContainer &&
        htmlContainer === "CSE_SYS" &&
        hasSizeDefinitions;

      if (everythingSet) {
        if (everythingValid) {
          fullExportStatusCheck();
          quickExportStatusCheck();

          if (callback) callback();
          return true;
        }
      }

      return false;
    }

    me.callParent(arguments);
    disableAttributeMapping();
    validatePluginConfig(enableAttributeMapping);
  }
});
