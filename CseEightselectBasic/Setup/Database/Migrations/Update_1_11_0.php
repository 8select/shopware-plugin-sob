<?php

namespace CseEightselectBasic\Setup\Database\Migrations;

use Shopware\Components\ConfigWriter;

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

    public function __construct(\Shopware_Components_Config $config, ConfigWriter $configWriter)
    {
        $this->config = $config;
        $this->configWriter = $configWriter;
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
        $this->configWriter->save(
            'CseEightselectBasicSysAccContainer',
            $this->config->get('8s_html_sysacc_container_element')
        );
    }
}
