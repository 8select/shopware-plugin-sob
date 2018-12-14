<?php
namespace CseEightselectBasic\Services\Export;

use Shopware;

class Export {

  public function __construct() {
    $container = Shopware()->Container();
    $this->configValidator = $container->get('cse_eightselect_basic.config.validator');
    $this->pluginConfig = $container->get('cse_eightselect_basic.dependencies.provider')->getPluginConfig();
    
  }

  private function isTidAndFidInRequest ($request) {
    $tenantId = $request->getHeader('8select-com-tid');
    $feedId = $request->getHeader('8select-com-fid');
    
    return $tenantId != null && $feedId != null;
  }

  private function isTidAndFidValid ($request) {
    return $request->getHeader('8select-com-tid') != $this->pluginConfig['8s_merchant_id'] || $request->getHeader('8select-com-fid') != $this->pluginConfig['8s_merchant_id'];
  }

  public function getOffsetAndLimit ($request) {
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

  public function validateExportRequest($response, $request) {
    $isTidAndFidInRequest = self::isTidAndFidInRequest($request);
    if (!$isTidAndFidInRequest) {
        $response->setHttpResponseCode(404);
        return false;
    }
    
    $validationResult = $this->configValidator->validateExportConfig();
    if (!$validationResult['isValid']) {
        $response->setHeader('Content-Type', 'application/json');
        $response->setBody(json_encode($validationResult['violations']));
        $response->setHttpResponseCode(500);
        return false;
    }
    
    $areTidAndFidValid = self::isTidAndFidValid($request);
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

  public function getArticlesByOffsetAndLimit($offsetAndLimit) {
    $offset = $offsetAndLimit['offset'];
    $limit = $offsetAndLimit['limit'];

    //return articles
  }
}
