Ext.define("Shopware.apps.CseEightselectBasicManualExport.view.detail.Export", {
    extend: "Ext.container.Container",
    alias: "widget.8select-export-detail-container",
    padding: 20,

    region: "center",
    cls: "shopware-form",
    layout: "vbox",

    initComponent: function () {

        Ext.Loader.setConfig({
            enabled: true,
            paths:{
                'Ext.configChecker':'../../../cse_eightselect_common/plugin_config_checker.js'
            }
        });

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
        
        var active = Ext.decode(stateCheck.responseText).active
        var apiId = Ext.decode(apiCheck.responseText).apiId
        var feedId = Ext.decode(feedCheck.responseText).feedId


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

        var EMPTY_API_ID_MESSAGE = "Keine API ID hinterlegt. Bitte überprüfen Sie Ihre Plugin-Einstellungen oder wenden Sie sich an 8select.";
        var INVALID_API_ID_MESSAGE = "API ID ungültig. Bitte überprüfen Sie Ihre Plugin-Einstellungen oder wenden Sie sich an 8select.";

        var EMPTY_FEED_ID_MESSAGE = "Keine Feed ID hinterlegt. Bitte überprüfen Sie Ihre Plugin-Einstellungen oder wenden Sie sich an 8select.";
        var INVALID_FEED_ID_MESSAGE = "Feed ID ungültig. Bitte überprüfen Sie Ihre Plugin-Einstellungen oder wenden Sie sich an 8select.";

        var PLUGIN_NOT_ACTIVE = "Plugin ist nicht aktiv. Bitte überprüfen Sie Ihre Plugin-Einstellungen oder wenden Sie sich an 8select.";


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

        function checkForActiveState(state) {
            if (!active || active === null) {
                Shopware.Notification.createGrowlMessage("", PLUGIN_NOT_ACTIVE);
            }
        }

        function checkForApiId(id) {
            if (!apiId || apiId === null || apiId.length === 0) {
                Shopware.Notification.createGrowlMessage("", EMPTY_API_ID_MESSAGE);
            }

            if (apiId && apiId !== null && apiId.length !== 36) {
                Shopware.Notification.createGrowlMessage("", INVALID_API_ID_MESSAGE);
            }
        }

        function checkForFeedId(id) {
            if (!feedId || feedId === null || feedId.length === 0) {
                Shopware.Notification.createGrowlMessage("", EMPTY_FEED_ID_MESSAGE);
            }

            if (feedId && feedId !== null && feedId.length !== 36) {
                Shopware.Notification.createGrowlMessage("", INVALID_FEED_ID_MESSAGE);
            }
        }

        function validatePluginConfig() {

            checkForActiveState(active)
            checkForApiId(apiId)
            checkForFeedId(feedId)

            if (active !== null && apiId !== null && feedId !== null) {
                if (active && apiId.length === 36 && feedId.length === 36) {
                    fullExportStatusCheck();
                    quickExportStatusCheck();

                    Ext.getCmp(QUICK_BTN.id).enable();
                    Ext.getCmp(FULL_BTN.id).enable();
                }
            }
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

        me.callParent(arguments);

        Ext.getCmp(QUICK_BTN.id).disable();
        Ext.getCmp(FULL_BTN.id).disable();

        validatePluginConfig();
    }
});
