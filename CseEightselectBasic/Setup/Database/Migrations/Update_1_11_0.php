<?php

namespace CseEightselectBasic\Setup\Database\Migrations;

use Shopware\Components\ConfigWriter;
use Shopware\Models\Shop\Shop;

class Update_1_11_0
{
    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var ConfigWrite
     */
    private $configWriter;

    /**
     * @var Shop
     */
    private $defaultShop;

    /**
     * @param \Shopware_Components_Config $config
     * @param ConfigWriter $configWriter
     * @param Shop $defaultShop
     */
    public function __construct(
        \Shopware_Components_Config $config,
        ConfigWriter $configWriter,
        Shop $defaultShop
    ) {
        $this->config = $config;
        $this->configWriter = $configWriter;
        $this->defaultShop = $defaultShop;
    }

    public function update()
    {
        $this->configWriter->save('CseEightselectBasicPluginActive', $this->config->get('8s_enabled'));
        $this->configWriter->save('CseEightselectBasicApiId', $this->config->get('8s_merchant_id'));
        $this->configWriter->save('CseEightselectBasicFeedId', $this->config->get('8s_feed_id'));
        $this->configWriter->save('CseEightselectBasicPreviewActive', $this->config->get('8s_preview_mode_enabled'));
        $this->configWriter->save('CseEightselectBasicSysPsvBlock', $this->config->get('8s_selected_detail_block'));
        $this->configWriter->save('CseEightselectBasicSysPsvPosition', $this->config->get('8s_widget_placement'));
        $this->configWriter->save('CseEightselectBasicCustomCss', $this->config->get('8s_custom_css'));
        $this->configWriter->save(
            'CseEightselectBasicSysPsvContainer',
            $this->config->get('8s_html_container_element')
        );
        $this->configWriter->save('CseEightselectBasicSysAccActive', $this->config->get('8s_sys_acc_enabled'));

        // when migrating from versions < 1.11.0 this config value does not exist
        // we can set the default - that is what happens if someone updates to 1.11.0
        $sysAccContainer = $this->config->get('8s_html_sysacc_container_element');
        if (!$sysAccContainer) {
            $sysAccContainer = '<![CDATA[<h1>Das passt dazu</h1> CSE_SYS]]>';
        }
        $this->configWriter->save(
            'CseEightselectBasicSysAccContainer',
            $sysAccContainer
        );
        $this->configWriter->save('CseEightselectBasicActiveShopId', $this->defaultShop->getId());
    }
}
