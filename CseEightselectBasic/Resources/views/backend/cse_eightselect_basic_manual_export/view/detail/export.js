Ext.define("Shopware.apps.CseEightselectBasicManualExport.view.detail.Export", {
  extend: "Ext.container.Container",
  alias: "widget.8select-export-detail-container",
  padding: 20,

  region: "center",
  cls: "shopware-form",
  layout: "vbox",

  initComponent: function() {
    var me = this;
    var interval;
    var BTN_TEXT_ENABLED = "Produkt Voll-Export anstoßen";
    var BTN_TEXT_DISABLED = "Produkt Voll-Export wird ausgeführt";
    var statusCheck = function() {
        Ext.Ajax.request({
            url:
                "{url controller=CseEightselectBasicManualExport action=getFullExportStatus}",
            success: function(response) {
                var progress = JSON.parse(response.responseText).progress;
                if (progress === false || progress === 100 || progress === "100") {
                    Ext.getCmp('full-export-btn').enable();
                    Ext.getCmp('full-export-btn').setText(BTN_TEXT_ENABLED);
                    clearInterval(interval);
                } else {
                    Ext.getCmp('full-export-btn').disable();
                    Ext.getCmp('full-export-btn').setText(BTN_TEXT_DISABLED + " (" + progress + "%)");
                }
            }
        });
    };

    me.items = [
      {
        text: BTN_TEXT_ENABLED,
        id: "full-export-btn",
        xtype: "button",
        scale: "large",
        width: "100%",

        handler: function() {
          Ext.Ajax.request({
            url:
              "{url controller=CseEightselectBasicManualExport action=fullExport}",
            failure: function() {
                Shopware.Notification.createGrowlMessage(
                    "",
                    "Es ist ein Fehler aufgetreten. Überprüfen Sie bitte Ihre Plugin-Einstellungen oder wenden Sie sich an 8select."
                );
            }
          });

          statusCheck();
          interval = setInterval(statusCheck, 5000);
        }
      },
      {
        text: "Produkt Schnell-Update anstoßen",
        id: "quick-export-btn",
        xtype: "button",
        scale: "large",
        width: "100%",

        handler: function() {
          Shopware.Notification.createGrowlMessage(
              "",
              "Der 8select Schnell-Update wird ausgeführt!"
          );
          Ext.getCmp('quick-export-btn').disable();
          Ext.Ajax.request({
            url:
              "{url controller=CseEightselectBasicManualExport action=quickExport}",
            success: function(response) {
              var text = Ext.decode(response.responseText);
              if (text.success) {
                Shopware.Notification.createGrowlMessage(
                  "",
                  "Der 8select Schnell-Update wurde abgeschlossen!"
                );
              } else {
                Shopware.Notification.createGrowlMessage(
                  "",
                  "Es ist ein Fehler aufgetreten. Überprüfen Sie bitte Ihre Plugin-Einstellungen oder wenden Sie sich an 8select."
                );
              }
            }
          });
        }
      }
    ];

    me.callParent(arguments);
    interval = setInterval(statusCheck, 5000);
  }
});
