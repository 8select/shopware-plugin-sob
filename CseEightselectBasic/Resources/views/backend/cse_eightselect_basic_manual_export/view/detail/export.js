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

        var lastFullExport = Ext.decode(requestLastFullExport.responseText).lastFullExport[0];
        var lastQuickUpdate = Ext.decode(requestLastQuickUpdate.responseText).lastQuickUpdate[0];

        var lastFullExportTimeStamp = lastFullExport && lastFullExport.last_run ? lastFullExport.last_run : '';
        var lastQuickUpdateTimeStamp = lastQuickUpdate && lastQuickUpdate.last_run ? lastQuickUpdate.last_run : '';

        var lastFullExportLabel =   lastFullExportTimeStamp.length === 0 ? 
                                    'Noch kein Voll-Export duchgeführt.' : 
                                    'Letzter Voll-Export am: ' + lastFullExportTimeStamp;

        var lastQuickUpdateLabel =  lastQuickUpdateTimeStamp.length === 0 ? 
                                    'Noch keine Schnell-Update durchgeführt.' : 
                                    'Letztes Schnell-Update am: ' + lastQuickUpdateTimeStamp;

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
        var ERROR_MESSAGE = "Es ist ein Fehler aufgetreten. Überprüfen Sie bitte Ihre Plugin-Einstellungen oder wenden Sie sich an 8select.";

        function statusCheck (actionUri, buttonId, buttonTextEnabled, buttonTextDisabled, callback) {
            Ext.Ajax.request({
                url: actionUri,
                success: function (response) {
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

        function fullExportStatusCheck () {
            statusCheck(FULL_BTN.statusUri, FULL_BTN.id, FULL_BTN.textEnabled, FULL_BTN.textDisabled, fullExportStatusCheck);
        }

        function quickExportStatusCheck () {
            statusCheck(QUICK_BTN.statusUri, QUICK_BTN.id, QUICK_BTN.textEnabled, QUICK_BTN.textDisabled, quickExportStatusCheck);
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
                            Shopware.Notification.createGrowlMessage("", ERROR_MESSAGE);
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
                            Shopware.Notification.createGrowlMessage("", ERROR_MESSAGE);
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
                margins: "30 0 0 0",
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
                margins: "5 0 15 0",
                style: {
                    textAlign: "center",
                    color: "#aaa",
                    fontSize: "11px"
                }
            }
        ];

        me.callParent(arguments);
        fullExportStatusCheck();
        quickExportStatusCheck();
    }
});
