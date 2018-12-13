Ext.define("Shopware.apps.CseEightselectBasicManualExport.view.detail.Export", {
  extend: "Ext.container.Container",
  alias: "widget.8select-export-detail-container",
  padding: 20,
  region: "center",
  cls: "shopware-form",
  layout: "vbox",

  initComponent: function() {
    var me = this;

    var configValidationRequest = Ext.Ajax.request({
      async: false,
      url:
        "{url controller=CseEightselectBasicConfigValidation action=validate}"
    });

    var getLastFullExportLabel = function () {
      var response = Ext.Ajax.request({
        async: false,
        url:
          "{url controller=CseEightselectBasicManualExport action=getLastFullExportDate}"
      });
      var lastFullExport = Ext.decode(response.responseText).lastFullExport;

      return !lastFullExport
        ? "Noch kein Voll-Export duchgeführt (alle Stammdaten)."
        : "Letzter Voll-Export am: " + lastFullExport + " (alle Stammdaten).";
    }

    var getLastPropertyExportLabel = function () {
      var response = Ext.Ajax.request({
        async: false,
        url: "{url controller=CseEightselectBasicManualExport action=getLastPropertyExportDate}"
      });
      var lastPropertyExport = Ext.decode(response.responseText).lastPropertyExport;

      return  !lastPropertyExport
        ? "Noch kein Schnell-Update durchgeführt oder es gab keine Änderungen."
        : "Letztes Schnell-Update am: " + lastPropertyExport + " (nur Änderungen).";
    }

    var configValidationResult = Ext.decode(
      configValidationRequest.responseText
    ).validationResult;

    var isConfigValid = function(configValidationResult) {
      return configValidationResult.isValid;
    };

    var getErrorMessages = function(configValidationResult) {
      return configValidationResult.violations;
    };

    var FULL_BTN = {
      id: "full-export-btn",
      textEnabled: "Produkt Voll-Export ausführen",
      textDisabled: "Produkt Voll-Export in Cron Warteschlange eingereiht",
      textProgress: "Produkt Voll-Export wird ausgeführt",
      exportUri:
        "{url controller=CseEightselectBasicManualExport action=fullExport}",
      statusUri:
        "{url controller=CseEightselectBasicManualExport action=getFullExportStatus}"
    };
    var PROPERTY_BTN = {
      id: "property-export-btn",
      textEnabled: "Produkt Schnell-Update ausführen",
      textDisabled: "Produkt Schnell-Update in Cron Warteschlange eingereiht",
      textProgress: "Produkt Schnell-Update wird ausgeführt",
      exportUri: "{url controller=CseEightselectBasicManualExport action=propertyExport}",
      statusUri: "{url controller=CseEightselectBasicManualExport action=getPropertyExportStatus}"
    };

    var statusCheck = function(
      actionUri,
      buttonId,
      buttonTextEnabled,
      buttonTextProgress,
      callback,
      updateTimeId,
      updateTimeCallback
    ) {
      Ext.Ajax.request({
        url: actionUri,
        success: function(response) {
          var button = Ext.getCmp(buttonId);
          var progress = JSON.parse(response.responseText).progress;
          var isRunning = JSON.parse(response.responseText).isRunning;
          if (progress === false || progress === 100) {
            button.enable();
            button.setText(buttonTextEnabled);
            Ext.getCmp(updateTimeId).setText(updateTimeCallback())
          } else if (isRunning) {
            button.disable();
            button.setText(buttonTextProgress + " (" + progress + "%)");
            setTimeout(callback, 5000);
          } else {
            setTimeout(callback, 5000);
          }
        }
      });
    };

    var fullExportStatusCheck = function() {
      statusCheck(
        FULL_BTN.statusUri,
        FULL_BTN.id,
        FULL_BTN.textEnabled,
        FULL_BTN.textProgress,
        fullExportStatusCheck,
        "last-full-export-timestamp",
        getLastFullExportLabel,
      );
    };

    var propertyExportStatusCheck = function() {
      statusCheck(
        PROPERTY_BTN.statusUri,
        PROPERTY_BTN.id,
        PROPERTY_BTN.textEnabled,
        PROPERTY_BTN.textProgress,
        propertyExportStatusCheck,
        "last-property-export-timestamp",
        getLastPropertyExportLabel,
      );
    };

    if (isConfigValid(configValidationResult)) {
      me.items = [
        {
          text: FULL_BTN.textEnabled,
          id: FULL_BTN.id,
          xtype: "button",
          scale: "large",
          width: "100%",
          margins: "50px 0 5px 0",

          handler: function() {
            Ext.getCmp(FULL_BTN.id).disable();
            Ext.getCmp(FULL_BTN.id).setText(FULL_BTN.textDisabled);
            Ext.Ajax.request({
              url: FULL_BTN.exportUri,
              failure: function() {
                Shopware.Notification.createStickyGrowlMessage({
                  title: "Export fehlgeschlagen",
                  text:
                    "Es ist ein Fehler aufgetreten. Bitte kontaktieren Sie 8select."
                });
              }
            });
            setTimeout(fullExportStatusCheck, 5000);
          }
        },
        {
          text: PROPERTY_BTN.textEnabled,
          id: PROPERTY_BTN.id,
          xtype: "button",
          scale: "large",
          width: "100%",
          margins: "5px 0 0 0",

          handler: function() {
            Ext.getCmp(PROPERTY_BTN.id).disable();
            Ext.getCmp(PROPERTY_BTN.id).setText(PROPERTY_BTN.textDisabled);
            Ext.Ajax.request({
              url: PROPERTY_BTN.exportUri,
              failure: function() {
                Shopware.Notification.createStickyGrowlMessage({
                  title: "Export fehlgeschlagen",
                  text:
                    "Es ist ein Fehler aufgetreten. Bitte kontaktieren Sie 8select."
                });
              }
            });
            setTimeout(propertyExportStatusCheck, 5000);
          }
        },
        {
          text: getLastFullExportLabel(),
          id: "last-full-export-timestamp",
          xtype: "label",
          width: "100%",
          margins: "30px 0px 0px 0px",
          style: {
            textAlign: "center",
            color: "#aaa",
            fontSize: "11px"
          }
        },
        {
          text: getLastPropertyExportLabel(),
          id: "last-property-export-timestamp",
          xtype: "label",
          width: "100%",
          margins: "5px 0px 15px 0px",
          style: {
            textAlign: "center",
            color: "#aaa",
            fontSize: "11px"
          }
        }
      ];

      fullExportStatusCheck();
      propertyExportStatusCheck();
    } else {
      me.items = [
        {
          xtype: "panel",
          region: "top",
          id: "pluginerrors",
          layout: "auto",
          width: 400,
          bodyPadding: 10,
          style: {
            backgroundColor: "#F8F9F9",
            margin: "0 0 20px 0"
          },
          html:
            "<h1>Funktion nicht verfügbar</h1><br><br>" +
            "<p>Bitte überprüfen Sie Ihre Plugin-Konfiguration. Details:<br></p>" +
            "<p>" +
            getErrorMessages(configValidationResult)
              .map(function(error) {
                return "<b>- " + error + "</b>";
              })
              .filter(Boolean)
              .join("<br>") +
            "</p>" +
            "<p><br><br>Benötigen Sie Hilfe? Kontaktieren Sie uns unter " +
            "<a href='mailto:onboarding@8select.de?subject=Shopware CSE Plugin: Fehlerhafte Plugin-Konfiguration'>onboarding@8select.de</a></p>"
        }
      ];
    }

    me.callParent(arguments);
  }
});
