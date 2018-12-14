<?php
namespace CseEightselectBasic\Components;

use Shopware;
class ExportHelper {
  private static function isTidAndFidInRequest ($request) {
    $tenantId = $request->getHeader('8select-com-tid');
    $feedId = $request->getHeader('8select-com-fid');
    
    return $tenantId != null && $feedId != null;
  }

  private static function isTidAndFidConfigured ($config) {
    $actualTenantId = $config['8s_merchant_id'];
    $actualFeedId = $config['8s_feed_id'];
  
    return $actualTenantId != null || $actualFeedId != null;
  }

  private static function isTidAndFidValid ($request, $config) {
    return $request->getHeader('8select-com-tid') != $config['8s_merchant_id'] || $request->getHeader('8select-com-fid') != $config['8s_merchant_id'];
  }

  public static function getOffsetAndLimit ($request) {
    //@todo maybe use symfony
    $MAX_LIMIT = 100;
    $limit = $request->getParam('limit', 50);
    $offset = $request->getParam('offset', 0);
  

    if(!is_numeric($limit) || !is_numeric($offset)) {
      return false;
    }

    $limit = intval($limit);
    $offset = intval($offset);
  
    if($limit > $MAX_LIMIT) {
      return false;
    }


    return ['limit' => $limit, 'offset' => $offset];
  }

  public static function validateExportRequest($response, $request) {
    $isTidAndFidInRequest = self::isTidAndFidInRequest($request);
    if (!$isTidAndFidInRequest) {
        $response->setHttpResponseCode(404);
        return false;
    }
    
    $config = Shopware()->Container()->get('cse_eightselect_basic.dependencies.provider')->getPluginConfig();
    $isTidAndFidConfigured = self::isTidAndFidConfigured($config);
    if (!$isTidAndFidConfigured) {
        $response->setHeader('Content-Type', 'text/html');
        $response->setBody('plugin is not configured');
        $response->setHttpResponseCode(500);
        return false;
    }
    
    $areTidAndFidValid = self::isTidAndFidValid($request, $config);
    if (!$areTidAndFidValid) {
        $response->setHeader('Content-Type', 'text/html');
        $response->setBody('wrong tid or fid');
        $response->setHttpResponseCode(400);
        return false;
    }
    
    $offsetAndLimit = self::getOffsetAndLimit($request);
    if (!$offsetAndLimit) {
        $response->setHeader('Content-Type', 'text/html');
        $response->setBody('offset or limit missing or no integer');
        $response->setHttpResponseCode(400);
        return false;
    }

    return true;
  } 

  public static function getArticlesByOffsetAndLimit($offsetAndLimit) {
    $offset = $offsetAndLimit['offset'];
    $limit = $offsetAndLimit['limit'];

    //return articles
  }
}
