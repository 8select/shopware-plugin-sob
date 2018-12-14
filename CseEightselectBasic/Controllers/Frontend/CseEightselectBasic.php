<?php

/**
 * Class Shopware_Controllers_Frontend_CseEightselectBasic
 *
 * @category    Shopware
 * @package     Shopware_Plugins
 * @subpackage  CseEightselectBasic
 * @author      Onedrop GmbH & Co KG
 */

use CseEightselectBasic\Components\ExportHelper;

class Shopware_Controllers_Frontend_CseEightselectBasic extends Enlight_Controller_Action
{
    /**
     * Provides the cart of the current user as JSON API.
     * The API is available at /cse-eightselect-basic/cart
     */
    public function cartAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Response()->setHeader('Content-Type', 'application/json');
        $this->Response()->setBody(
            json_encode(['basket' => Shopware()->Modules()->Basket()->sGetBasket(), 'success' => true])
        );
        Shopware()->Plugins()->Controller()->Json()->setPadding();
    }

    /**
     * The API is available at /eight-select/export
     */
    public function exportAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        
        $isTidAndFidInRequest = ExportHelper::isTidAndFidInRequest($this->Request());
        if (!$isTidAndFidInRequest) {
            $this->Response()->setBody('not found');
            $this->Response()->setHttpResponseCode(404);
            return false;
        }
        
        $config = Shopware()->Container()->get('cse_eightselect_basic.dependencies.provider')->getPluginConfig();
        $isTidAndFidConfigured = ExportHelper::isTidAndFidConfigured($config);
        if (!$isTidAndFidConfigured) {
            $this->Response()->setHeader('Content-Type', 'text/html');
            $this->Response()->setBody('plugin is not configured');
            $this->Response()->setHttpResponseCode(500);
            return false;
        }
        
        $areTidAndFidValid = ExportHelper::isTidAndFidValid($this->Request(), $config);
        if (!$areTidAndFidValid) {
            $this->Response()->setHeader('Content-Type', 'text/html');
            $this->Response()->setBody('wrong tid or fid');
            $this->Response()->setHttpResponseCode(400);
            return false;
        }
        
        $offsetAndLimit = ExportHelper::getOffsetAndLimit($this->Request());
        if (!$offsetAndLimit) {
            $this->Response()->setHeader('Content-Type', 'text/html');
            $this->Response()->setBody('offset or limit missing');
            $this->Response()->setHttpResponseCode(400);
            return false;
        }

        $this->Response()->setHeader('Content-Type', 'text/html');
        $this->Response()->setBody('request looks ok');
    }
}
