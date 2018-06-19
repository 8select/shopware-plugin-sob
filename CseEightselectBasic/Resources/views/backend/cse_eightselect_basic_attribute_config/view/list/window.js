Ext.define("Shopware.apps.CseEightselectBasicAttributeConfig.view.list.Window", {
  extend: "Shopware.window.Listing",
  alias: "widget.8select-attributes-window",
  height: 610,
  title: "{s name=window_title}8select Attribute Mapping{/s}",
  id: "EightSelectAttributeMapping",

  configure: function() {
    return {
      listingGrid: "Shopware.apps.CseEightselectBasicAttributeConfig.view.list.EightselectAttribute",
      listingStore: "Shopware.apps.CseEightselectBasicAttributeConfig.store.EightselectAttribute"
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

    var ERORR_MESSAGES = validationDebugInfo();

    if (!validatePluginConfig()) {
      Ext.define("Shopware.apps.CseEightselectBasicAttributeConfig.view.list.error.Window", {
        extend: "Enlight.app.Window",
        title: "Plugin Konfiguration ungültig",
        cls: "plugin-error-modal",
        width: 450,
        maxHeight: 300,
        items: [
          {
            xtype: "panel",
            region: "center",
            id: "pluginerrors",
            layout: "auto",
            width: 400,
            bodyPadding: 10,
            style: {
              backgroundColor: "#F8F9F9",
              margin: "20px"
            },
            html:
              "<h1>Funktion nicht verfügbar</h1><br><br>" +
              "<p>Bitte überprüfen Sie Ihre Plugin-Konfiguration. Details:<br></p>" +
              "<p>" +
              ERORR_MESSAGES.map(function(error) {
                if (!error) {
                  return;
                }
                return "<b>- " + error + "</b>";
              })
                .filter(Boolean)
                .join("<br>") +
              "</p>" +
              "<p><br><br>Benötigen Sie Hilfe? Kontaktieren Sie uns unter " +
              "<a href='mailto:onboarding@8select.de?subject=Shopware CSE Plugin: Fehlerhafte Plugin-Konfiguration'>onboarding@8select.de</a></p>"
          }
        ],
        listeners: {
          close: function() {
            me.close();
          },
          hide: function() {
            me.hide();
          },
          afterrender: function() {
            me.close();
          }
        }
      });

      Ext.create("Shopware.apps.CseEightselectBasicAttributeConfig.view.list.error.Window").show().toFront();
    }    

    me.callParent(arguments);
  }
});
