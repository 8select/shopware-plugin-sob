Ext.define(
  "Shopware.apps.EightselectCSEAttributeConfig.model.EightselectAttribute",
  {
    extend: "Ext.data.Model",

    fields: [
      { name: "id", type: "int" },
      { name: "eightselectAttribute", type: "string" },
      { name: "shopwareAttribute", type: "string" }
    ],

    proxy: {
      type: "ajax",

      /**
       * Configure the url mapping for the different
       * store operations based on
       * @object
       */
      api: {
        read: "{url controller=EightselectCSEAttributeConfig action=list}",
        update: "{url controller=EightselectCSEAttributeConfig action=update}"
      },
      /**
       * Configure the data reader
       * @object
       */
      reader: {
        type: "json",
        root: "data"
      }
    }
  }
);
