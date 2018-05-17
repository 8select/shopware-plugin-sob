Ext.define(
  "Shopware.apps.CseEightselectBasicManualExport.model.CseEightselectBasicExport",
  {
    extend: "Ext.data.Model",

    fields: [
      { name: "fullexport", type: "string" },
      { name: "quickexport", type: "string" }
    ]
  }
);
