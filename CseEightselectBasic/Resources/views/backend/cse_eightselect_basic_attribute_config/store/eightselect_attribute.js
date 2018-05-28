Ext.define(
  "Shopware.apps.CseEightselectBasicAttributeConfig.store.EightselectAttribute",
  {
    extend: "Ext.data.Store",

    model:
      "Shopware.apps.CseEightselectBasicAttributeConfig.model.EightselectAttribute",
    groupField: 'eightselectAttributeGroupName'
  }
);
