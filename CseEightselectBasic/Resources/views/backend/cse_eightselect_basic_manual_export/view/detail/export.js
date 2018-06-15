Ext.define("Shopware.apps.CseEightselectBasicManualExport.view.detail.Export", {
    extend: "Ext.container.Container",
    alias: "widget.8select-export-detail-container",
    padding: 20,

    region: "center",
    cls: "shopware-form",
    layout: "vbox",

    initComponent: function () {
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

        var lastFullExportTimeStamp = lastFullExport ? lastFullExport : '';
        var lastQuickUpdateTimeStamp = lastQuickUpdate ? lastQuickUpdate : '';

        var lastFullExportLabel =   lastFullExportTimeStamp.length === 0 ? 
                                    'Noch kein Voll-Export duchgeführt.' : 
                                    'Letzter Voll-Export am: ' + lastFullExportTimeStamp;

        var lastQuickUpdateLabel =  lastQuickUpdateTimeStamp.length === 0 ? 
                                    'Noch keine Schnell-Update durchgeführt.' : 
                                    'Letztes Schnell-Update am: ' + lastQuickUpdateTimeStamp;
        var stateCheck = Ext.Ajax.request({ async: false, url: '{url controller=CseEightselectBasicManualExport action=checkForActiveState}' })
        var apiCheck = Ext.Ajax.request({ async: false, url: '{url controller=CseEightselectBasicManualExport action=checkForApiId}' })
        var feedCheck = Ext.Ajax.request({ async: false, url: '{url controller=CseEightselectBasicManualExport action=checkForFeedId}' })
        var htmlContainerCheck = Ext.Ajax.request({ async: false, url: '{url controller=CseEightselectBasicManualExport action=checkForHtmlContainer}' })
        var sysAccCheck = Ext.Ajax.request({ async: false, url: '{url controller=CseEightselectBasicManualExport action=checkForSysAcc}' })
        var previewCheck = Ext.Ajax.request({ async: false, url: '{url controller=CseEightselectBasicManualExport action=checkForPreviewMode}' })
        var sizesCheck = Ext.Ajax.request({ async: false, url: '{url controller=CseEightselectBasicManualExport action=checkForSizeDefinitions}' })

        var active = Ext.decode(stateCheck.responseText).active
        var apiId = Ext.decode(apiCheck.responseText).apiId
        var feedId = Ext.decode(feedCheck.responseText).feedId
        var htmlContainer = Ext.decode(htmlContainerCheck.responseText).container
        var sysAcc = Ext.decode(sysAccCheck.responseText).sysAcc
        var previewMode = Ext.decode(previewCheck.responseText).previewMode
        var hasSizeDefinitions = Ext.decode(sizesCheck.responseText).sizeDefinitions

        var FULL_BTN = {
            id: 'full-export-btn',
            textEnabled: 'Produkt Voll-Export anstoßen',
            textDisabled: 'Produkt Voll-Export wird ausgeführt',
            exportUri: '{url controller=CseEightselectBasicManualExport action=fullExport}',
            statusUri: '{url controller=CseEightselectBasicManualExport action=getFullExportStatus}',
        };
        var QUICK_BTN = {
            id: 'quick-export-btn',
            textEnabled: 'Produkt Schnell-Update anstoßen',
            textDisabled: 'Produkt Schnell-Update wird ausgeführt',
            exportUri: '{url controller=CseEightselectBasicManualExport action=quickExport}',
            statusUri: '{url controller=CseEightselectBasicManualExport action=getQuickExportStatus}',
        };

        function pluginGrowlMessage(message, helpUrl) {
          var callEightselect = "Bitte überprüfen Sie Ihre Plugin-Einstellungen oder wenden Sie sich an 8select.";
          var fullNotification = helpUrl ? callEightselect + "<br><br><a href='" + helpUrl + "' target='_blank'>Hilfe</a>" : callEightselect;
    
          Shopware.Notification.createGrowlMessage(message, fullNotification);
        }

    function statusCheck(actionUri, buttonId, buttonTextEnabled, buttonTextDisabled, callback) {
      Ext.Ajax.request({
        url: actionUri,
        success: function(response) {
          var button = Ext.getCmp(buttonId);
          var progress = JSON.parse(response.responseText).progress;
          if (progress === false || progress === 100 || progress === "100") {
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

    function checkForActiveState(validateState) {
      if (!validateState || validateState === null) {
        pluginGrowlMessage("Plugin ist nicht aktiv.");
      }
    }

    function checkForApiId(id) {
      if (!id || id === null || id.length === 0) {
        pluginGrowlMessage("Keine API ID hinterlegt.");
      }
      if (id && id !== null && id.length !== 36) {
        pluginGrowlMessage("API ID ist ungültig.");
      }
    }

    function checkForFeedId(id) {
      if (!id || id === null || id.length === 0) {
        pluginGrowlMessage("Keine Feed ID hinterlegt.");
      }
      if (id && id !== null && id.length !== 36) {
        pluginGrowlMessage("Feed ID ist ungültig.");
      }
    }

    function checkForHtmlContainer(container) {
      if (!container || container === null || container.length === 0) {
        pluginGrowlMessage("Kein Widget-Platzhalter im HTML-Container.");
      }
      if (container && container !== null && container !== "CSE_SYS") {
        pluginGrowlMessage("Widget-Platzhalter im HTML-Container ist ungültig.");
      }
    }

    function checkForSysAccAction(option) {
      if (option === null) {
        pluginGrowlMessage("Keine Einstellung für SYS-ACC Widget hinterlegt.");
      }
    }

    function checkForPreviewMode(mode) {
      if (mode === null) {
        pluginGrowlMessage("Keine Einstellung für Vorschau-Modus hinterlegt.");
      }
    }

    function checkForSizeDefinitions(hasSizes) {
      if (!hasSizes) {
        var docsUrl = "https://www.8select.com/8select-cse-installationsanleitung-shopware#5-konfiguration-attributfelder"
        pluginGrowlMessage("Keine Attributgruppe als Größe definiert.", docsUrl);
      }
    }

    function validatePluginConfig(callback) {
      checkForActiveState(active);
      checkForApiId(apiId);
      checkForFeedId(feedId);
      checkForHtmlContainer(htmlContainer);
      checkForSysAccAction(sysAcc);
      checkForPreviewMode(previewMode);
      checkForSizeDefinitions(hasSizeDefinitions);

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

          callback();
        }
      }
    }

    function enableManualExport() {
      Ext.getCmp(QUICK_BTN.id).enable();
      Ext.getCmp(FULL_BTN.id).enable();
    }

    function disableManualExport() {
      Ext.getCmp(QUICK_BTN.id).disable();
      Ext.getCmp(FULL_BTN.id).disable();
    }

        me.items = [
            {
                text: FULL_BTN.textEnabled,
                id: FULL_BTN.id,
                xtype: "button",
                scale: "large",
                width: "100%",

                handler: function () {
                    Ext.getCmp(FULL_BTN.id).disable();
                    Ext.getCmp(FULL_BTN.id).setText(FULL_BTN.textDisabled + " (0%)");
                    Ext.Ajax.request({
                        url: FULL_BTN.exportUri,
                        failure: function () {
                            pluginGrowlMessage("Es ist ein Fehler aufgetreten.")
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

                handler: function () {
                    Ext.getCmp(QUICK_BTN.id).disable();
                    Ext.getCmp(QUICK_BTN.id).setText(QUICK_BTN.textDisabled + " (0%)");
                    Ext.Ajax.request({
                        url: QUICK_BTN.exportUri,
                        failure: function () {
                            pluginGrowlMessage("Es ist ein Fehler aufgetreten.")
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
          });
          setTimeout(quickExportStatusCheck, 5000);
        }
      }
    ];

    me.callParent(arguments);
    disableManualExport();
    validatePluginConfig(enableManualExport);
  }
});
