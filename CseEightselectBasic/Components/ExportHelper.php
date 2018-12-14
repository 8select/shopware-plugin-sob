<?php
namespace CseEightselectBasic\Components;

use Shopware;
class ExportHelper {
  public static function isTidAndFidInRequest ($request) {
    $tenantId = $request->getHeader('8select-com-tid');
    $feedId = $request->getHeader('8select-com-fid');
    
    return $tenantId != null && $feedId != null;
  }

  public static function isTidAndFidConfigured ($config) {
    $actualTenantId = $config['8s_merchant_id'];
    $actualFeedId = $config['8s_feed_id'];
  
    return $actualTenantId != null || $actualFeedId != null;
  }

  public static function isTidAndFidValid ($request, $config) {
    return $request->getHeader('8select-com-tid') != $config['8s_merchant_id'] || $request->getHeader('8select-com-fid') != $config['8s_merchant_id'];
  }

  public static function getOffsetAndLimit ($request) {
    $limit = $request->getParam('limit');
    $offset = $request->getParam('offset');

    dump($limit);

    if ($limit == null || $offset == null) {
      return false;
    }

    return ['limit' => $limit, 'offset' => $offset];
  }
}
