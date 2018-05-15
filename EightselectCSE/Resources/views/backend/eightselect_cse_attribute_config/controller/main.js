Ext.define("Shopware.apps.EightselectCSEAttributeConfig.controller.Main", {
  extend: "Enlight.app.Controller",

  init: function() {
    var me = this;
    me.mainWindow = me
      .getView("list.Window")
      .create({})
      .show();

    me.control({
      "8select-attributes-grid": {
        edit: me.editRow
      }
    });
  },

  editRow: function(event, context) {
    context.record.save();
  }
});
