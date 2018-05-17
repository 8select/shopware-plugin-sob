Ext.define("Shopware.apps.CseEightselectBasicManualExport.view.detail.Export", {
  extend: "Ext.container.Container",
  alias: "widget.8select-export-detail-container",
  padding: 20,

  region: "center",
  cls: "shopware-form",
  layout: "vbox",

  initComponent: function() {
    var me = this;

    me.items = [
      {
        text: "Produkt Voll-Export anstoßen",
        xtype: "button",
        scale: "large",
        width: "100%",

        handler: function() {
          Shopware.Notification.createGrowlMessage(
              "",
              "Der 8select Produkt-Export wird ausgeführt!"
          );
          Ext.Ajax.request({
            url:
              "{url controller=CseEightselectBasicManualExport action=fullExport}",
            success: function(response) {
              var text = Ext.decode(response.responseText);
              if (text.success) {
                Shopware.Notification.createGrowlMessage(
                  "",
                  "Der 8select Produkt-Export wurde abgeschlossen!"
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
      },
      {
        text: "Produkt Schnell-Update anstoßen",
        xtype: "button",
        scale: "large",
        width: "100%",

        handler: function() {
          Shopware.Notification.createGrowlMessage(
              "",
              "Der 8select Schnell-Update wird ausgeführt!"
          );
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
  }
});
