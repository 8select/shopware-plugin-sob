Ext.define('Shopware.apps.EightSelectManualExport.view.detail.Export', {
    extend: 'Ext.container.Container',
    alias: 'widget.8select-export-detail-container',
    padding: 20,

    region: 'center',
    cls: 'shopware-form',
    layout: 'vbox',

    snippets : {
        successTitle: 'success'
    },

    initComponent: function () {
        var me = this;

        me.items = [
            {
                text: 'Voll export',
                xtype: 'button',
                scale: 'large',

                handler: function() {
                    Ext.Ajax.request({
                        url: '{url controller=EightSelectManualExport action=fullExport}',
                        success: function(response) {
                            var text = Ext.JSON.decode(response.responseText);
                            if (text.success) {
                                Shopware.Notification.createGrowlMessage('', me.snippets.successTitle, 'SuccessMessage');
                            } else {
                                Shopware.Notification.createGrowlMessage('', 'Error', 'ErrorMessage');
                            }
                        }

                    });
                }
            }, {
                text: 'Quick export',
                xtype: 'button',
                scale: 'large',

                handler: function() {
                    Ext.Ajax.request({
                        url: '{url controller=EightSelectManualExport action=quickExport}',
                        success: function(response) {
                            var text = Ext.JSON.decode(response.responseText);
                            if (text.success) {
                                Shopware.Notification.createGrowlMessage('', me.snippets.successTitle, 'SuccessMessage');
                            } else {
                                Shopware.Notification.createGrowlMessage('', 'Error', 'ErrorMessage');
                            }
                        }

                    });
                }
            }
        ];

        me.callParent(arguments);
    }
});
