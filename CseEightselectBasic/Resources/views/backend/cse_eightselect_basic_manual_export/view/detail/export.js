Ext.define("Shopware.apps.CseEightselectBasicManualExport.view.detail.Export", {
  extend: "Ext.container.Container",
  alias: "widget.8select-export-detail-container",
  padding: 20,
  region: "center",
  cls: "shopware-form",
  layout: "vbox",

  initComponent: function() {
    var me = this;

    var requestLastFullExport = Ext.Ajax.request({
      async: false,
      url: "{url controller=CseEightselectBasicManualExport action=getLastFullExportDate}"
    });

    var requestLastQuickUpdate = Ext.Ajax.request({
      async: false,
      url: "{url controller=CseEightselectBasicManualExport action=getLastQuickUpdateDate}"
    });

    var lastFullExport = Ext.decode(requestLastFullExport.responseText).lastFullExport;
    var lastQuickUpdate = Ext.decode(requestLastQuickUpdate.responseText).lastQuickUpdate;

    var lastFullExportTimeStamp = lastFullExport ? lastFullExport : "";
    var lastQuickUpdateTimeStamp = lastQuickUpdate ? lastQuickUpdate : "";

    var lastFullExportLabel =
      lastFullExportTimeStamp.length === 0
        ? "Noch kein Voll-Export duchgeführt."
        : "Letzter Voll-Export am: " + lastFullExportTimeStamp;

    var lastQuickUpdateLabel =
      lastQuickUpdateTimeStamp.length === 0
        ? "Noch keine Schnell-Update durchgeführt."
        : "Letztes Schnell-Update am: " + lastQuickUpdateTimeStamp;

    var configValidationResult = Ext.decode(configValidationRequest.responseText).validationResult;

    var isConfigValid = function(configValidationResult) {
      return configValidationResult.isValid;
    };

    var getErrorMessages = function(configValidationResult) {
      return configValidationResult.messages;
    };

    var FULL_BTN = {
      id: "full-export-btn",
      textEnabled: "Produkt Voll-Export ausführen",
      textDisabled: "Produkt Voll-Export wird ausgeführt",
      exportUri: "{url controller=CseEightselectBasicManualExport action=fullExport}",
      statusUri: "{url controller=CseEightselectBasicManualExport action=getFullExportStatus}"
    };
    var QUICK_BTN = {
      id: "quick-export-btn",
      textEnabled: "Produkt Schnell-Update ausführen",
      textDisabled: "Produkt Schnell-Update wird ausgeführt",
      exportUri: "{url controller=CseEightselectBasicManualExport action=quickExport}",
      statusUri: "{url controller=CseEightselectBasicManualExport action=getQuickExportStatus}"
    };

    function statusCheck(actionUri, buttonId, buttonTextEnabled, buttonTextDisabled, callback) {
      Ext.Ajax.request({
        url: actionUri,
        success: function(response) {
          var button = Ext.getCmp(buttonId);
          var progress = JSON.parse(response.responseText).progress;
          if ( !progress || progress === 100 ) {
            button.enable();
            button.setText(buttonTextEnabled);
          } else {
            button.disable();
            button.setText(buttonTextDisabled + " (" + progress + "%)");
            setTimeout(callback, 5000);
          }
        }
      });
    }

    function fullExportStatusCheck() {
      statusCheck(FULL_BTN.statusUri, FULL_BTN.id, FULL_BTN.textEnabled, FULL_BTN.textDisabled, fullExportStatusCheck);
    }

    function quickExportStatusCheck() {
      statusCheck(
        QUICK_BTN.statusUri,
        QUICK_BTN.id,
        QUICK_BTN.textEnabled,
        QUICK_BTN.textDisabled,
        quickExportStatusCheck
      );
    }

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
            Ext.getCmp(FULL_BTN.id).setText(FULL_BTN.textDisabled + " (0%)");
            Ext.Ajax.request({
              url: FULL_BTN.exportUri,
              failure: function() {
                Shopware.Notification.createStickyGrowlMessage({
                  title: "Export fehlgeschlagen",
                  text: "Es ist ein Fehler aufgetreten. Bitte kontaktieren Sie 8select."
                });
              }
            });
            setTimeout(fullExportStatusCheck, 5000);
          }
        },
        {
          text: QUICK_BTN.textEnabled,
          id: QUICK_BTN.id,
          xtype: "button",
          scale: "large",
          width: "100%",
          margins: "5px 0 0 0",

          handler: function() {
            Ext.getCmp(QUICK_BTN.id).disable();
            Ext.getCmp(QUICK_BTN.id).setText(QUICK_BTN.textDisabled + " (0%)");
            Ext.Ajax.request({
              url: QUICK_BTN.exportUri,
              failure: function() {
                Shopware.Notification.createStickyGrowlMessage({
                  title: "Export fehlgeschlagen",
                  text: "Es ist ein Fehler aufgetreten. Bitte kontaktieren Sie 8select."
                });
              }
            });
            setTimeout(quickExportStatusCheck, 5000);
          }
        },
        {
          text: lastFullExportLabel,
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
          text: lastQuickUpdateLabel,
          id: "last-quick-update-timestamp",
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
      quickExportStatusCheck();
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
