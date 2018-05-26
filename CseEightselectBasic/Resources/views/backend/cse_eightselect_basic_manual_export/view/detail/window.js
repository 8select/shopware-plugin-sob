Ext.define("Shopware.apps.CseEightselectBasicManualExport.view.detail.Window", {
  extend: "Enlight.app.Window",
  alias: "widget.8select-export-window",
  width: 450,
  height: 200,
  title: "{s name=window_title}8select Manual Export{/s}",
  layout: "border",

  initComponent: function() {
    var me = this;

    me.items = [
      Ext.create(
        "Shopware.apps.CseEightselectBasicManualExport.view.detail.Export"
      )
    ];

    me.callParent(arguments);
  }
});
