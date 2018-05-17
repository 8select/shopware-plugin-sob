Ext.define("Shopware.apps.CseEightselectBasicManualExport", {
  extend: "Enlight.app.SubApplication",

  name: "Shopware.apps.CseEightselectBasicManualExport",

  loadPath: "{url action=load}",
  bulkLoad: true,

  controllers: ["Main"],

  views: ["detail.Window", "detail.Export"],

  launch: function() {
    return this.getController("Main").mainWindow;
  }
});
