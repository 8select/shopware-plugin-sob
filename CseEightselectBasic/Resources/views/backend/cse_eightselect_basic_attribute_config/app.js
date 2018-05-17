Ext.define("Shopware.apps.CseEightselectBasicAttributeConfig", {
  extend: "Enlight.app.SubApplication",

  name: "Shopware.apps.CseEightselectBasicAttributeConfig",

  loadPath: "{url action=load}",
  bulkLoad: true,

  controllers: ["Main"],

  views: ["list.Window", "list.EightselectAttribute"],

  models: ["EightselectAttribute", "ShopwareAttribute"],
  stores: ["EightselectAttribute", "ShopwareAttribute"],

  launch: function() {
    return this.getController("Main").mainWindow;
  }
});
