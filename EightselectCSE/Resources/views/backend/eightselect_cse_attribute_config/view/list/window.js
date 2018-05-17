Ext.define("Shopware.apps.EightselectCSEAttributeConfig.view.list.Window", {
  extend: "Shopware.window.Listing",
  alias: "widget.8select-attributes-window",
  height: 450,
  title: "{s name=window_title}8select Attribute Mapping{/s}",

  configure: function() {
    return {
      listingGrid:
        "Shopware.apps.EightselectCSEAttributeConfig.view.list.EightselectAttribute",
      listingStore:
        "Shopware.apps.EightselectCSEAttributeConfig.store.EightselectAttribute"
    };
  }
});
