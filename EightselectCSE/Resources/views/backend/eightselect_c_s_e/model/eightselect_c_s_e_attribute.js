Ext.define("Shopware.apps.EightselectCSE.model.EightSelectAttribute", {
  extend: "Ext.data.Model",

  fields: [
    { name: "id", type: "int" },
    { name: "eightSelectAttribute", type: "string" },
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
      read: "{url controller=EightselectCSE action=list}",
      update: "{url controller=EightselectCSE action=update}"
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
});
