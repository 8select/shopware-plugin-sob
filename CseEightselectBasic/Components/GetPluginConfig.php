<?php
namespace CseEightselectBasic\Components;

class GetPluginConfig {

  /**
  * @return boolean
  */
  public static function getActiveState()
  {
    $config = Shopware()->Container()->get('shopware.plugin.cached_config_reader');
    $isActive = $config->getByPluginName('CseEightselectBasic')['8s_enabled'];
    return $isActive;
  }

  /**
  * @return string
  */
  public static function getApiId()
  {
    $config = Shopware()->Container()->get('shopware.plugin.cached_config_reader');
    $apiId = $config->getByPluginName('CseEightselectBasic')['8s_merchant_id'];
    return $apiId;
  }

  /**
  * @return string
  */
  public static function getFeedId()
  {
    $config = Shopware()->Container()->get('shopware.plugin.cached_config_reader');
    $feedId = $config->getByPluginName('CseEightselectBasic')['8s_feed_id'];
    return $feedId;
  }

  /**
  * @return string
  */
  public static function getHtmlContainer()
  {
    $config = Shopware()->Container()->get('shopware.plugin.cached_config_reader');
    $container = $config->getByPluginName('CseEightselectBasic')['8s_html_container_element'];
    return $container;
  }

  /**
  * @return boolean
  */
  public static function getSysAcc()
  {
    $config = Shopware()->Container()->get('shopware.plugin.cached_config_reader');
    $sysAcc = $config->getByPluginName('CseEightselectBasic')['8s_sys_acc_enabled'];
    return $sysAcc;
  }

  /**
  * @return boolean
  */
  public static function getPreviewMode()
  {
    $config = Shopware()->Container()->get('shopware.plugin.cached_config_reader');
    $previewMode = $config->getByPluginName('CseEightselectBasic')['8s_preview_mode_enabled'];
    return $previewMode;
  }
}
