<?php
namespace CseEightselectBasic\Components;

class GetPluginConfig {

  /**
  * @return string
  */
  public static function getActiveState()
  {
    $config = Shopware()->Container()->get('shopware.plugin.config_reader');
    $isActive = $config->getByPluginName('CseEightselectBasic')['8s_enabled'];
    return $isActive;
  }

  /**
  * @return string
  */
  public static function getApiId()
  {
    $config = Shopware()->Container()->get('shopware.plugin.config_reader');
    $apiId = $config->getByPluginName('CseEightselectBasic')['8s_merchant_id'];
    return $apiId;
  }

  /**
  * @return string
  */
  public static function getFeedId()
  {
    $config = Shopware()->Container()->get('shopware.plugin.config_reader');
    $feedId = $config->getByPluginName('CseEightselectBasic')['8s_feed_id'];
    return $feedId;
  }
}
