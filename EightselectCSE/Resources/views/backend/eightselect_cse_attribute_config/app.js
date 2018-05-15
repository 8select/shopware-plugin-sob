Ext.define("Shopware.apps.EightselectCSEAttributeConfig", {
  extend: "Enlight.app.SubApplication",

  name: "Shopware.apps.EightselectCSEAttributeConfig",

  loadPath: "{url action=load}",
  bulkLoad: true,

  controllers: ["Main"],

  views: ["list.Window", "list.EightselectCSEAttribute"],

  models: ["EightselectCSEAttribute", "ShopwareAttribute"],
  stores: ["EightselectCSEAttribute", "ShopwareAttribute"],

  launch: function() {
    return this.getController("Main").mainWindow;
  }
});
