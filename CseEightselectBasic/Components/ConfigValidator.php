<?php
namespace CseEightselectBasic\Components;

class ConfigValidator {

  /**
   * @return object
   */
  private static function getCseEightselectBasicConfiguration() {
    return Shopware()->Container()->get('shopware.plugin.cached_config_reader')->getByPluginName('CseEightselectBasic');
  }

  /**
  * @return boolean
  */
  public static function isPluginActive()
  {
    return self::getCseEightselectBasicConfiguration()['8s_enabled'];
  }

  /**
  * @return string
  */
  public static function getApiId()
  {
    return self::getCseEightselectBasicConfiguration()['8s_merchant_id'];
  }

  /**
  * @return string
  */
  public static function getFeedId()
  {
    return self::getCseEightselectBasicConfiguration()['8s_feed_id'];
  }

  /**
  * @return string
  */
  public static function getHtmlContainer()
  {
    return self::getCseEightselectBasicConfiguration()['8s_html_container_element'];
  }

  /**
  * @return boolean
  */
  public static function isSysAccActive()
  {
    return self::getCseEightselectBasicConfiguration()['8s_sys_acc_enabled'];
  }

  /**
  * @return boolean
  */
  public static function isPreviewModeActive()
  {
    return self::getCseEightselectBasicConfiguration()['8s_preview_mode_enabled'];
  }
  
  /**
   * @return boolean
   */
  public static function hasSizeDefinitions()
  {
     return Shopware()->Db()->fetchOne('SELECT SUM(od_cse_eightselect_basic_is_size = 1) FROM s_article_configurator_groups_attributes');
  }

  /**
  * @return boolean
  * @throws \Exception
  */
  public static function isConfigValid()
  {  
      $isActive = self::isPluginActive();
      $apiId = self::getApiId();
      $feedId = self::getFeedId();

      $configIsValid = $isActive && $apiId && $feedId && strlen($apiId) == 36 && strlen($feedId) == 36;
      return $configIsValid;
  }

}
