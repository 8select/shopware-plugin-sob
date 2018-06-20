Ext.define(
  "Shopware.apps.CseEightselectBasicAttributeConfig.view.list.Window",
  {
    extend: "Shopware.window.Listing",
    alias: "widget.8select-attributes-window",
    height: 610,
    title: "{s name=window_title}8select Attribute Mapping{/s}",
    id: "EightSelectAttributeMapping",

    configure: function() {
      return {
        listingGrid:
          "Shopware.apps.CseEightselectBasicAttributeConfig.view.list.EightselectAttribute",
        listingStore:
          "Shopware.apps.CseEightselectBasicAttributeConfig.store.EightselectAttribute"
      };
    },

    initComponent: function() {
      var me = this;

      var configValidationRequest = Ext.Ajax.request({
        async: false,
        url:
          "{url controller=CseEightselectBasicConfigValidation action=validate}"
      });
      var configValidationResult = Ext.decode(
        configValidationRequest.responseText
      ).validationResult;

      var isConfigValid = function(configValidationResult) {
        return configValidationResult.isValid;
      };

      var getErrorMessages = function(configValidationResult) {
        return configValidationResult.messages;
      };

      if (!isConfigValid(configValidationResult)) {
        Ext.define(
          "Shopware.apps.CseEightselectBasicAttributeConfig.view.list.error.Window",
          {
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
          }
        );

        Ext.create(
          "Shopware.apps.CseEightselectBasicAttributeConfig.view.list.error.Window"
        )
          .show()
          .toFront();
      }

      me.callParent(arguments);
    }
  }
);
