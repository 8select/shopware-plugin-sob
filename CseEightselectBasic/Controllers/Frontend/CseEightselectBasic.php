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
     * The API is available at /eight-select/exportProduct
     */
    public function exportProductAction()
    {
        try {
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
            $requestWasValid = ExportHelper::validateExportRequest($this->Response(), $this->Request());

            if (!$requestWasValid) {
                return false;
            }
            
            $offsetAndLimit = ExportHelper::getOffsetAndLimit($this->Request());

            $this->Response()->setHeader('Content-Type', 'text/html');
            $this->Response()->setBody('request looks ok');
            return true;
        } catch (Exception $e) {
            $this->Response()->setHeader('Content-Type', 'text/html');
            $this->Response()->setBody($e->getMessage());
            $this->Response()->setHttpResponseCode(500);
        }
    }

    /**
     * The API is available at /eight-select/exportProperty
     */
    public function exportPropertyAction()
    {
        try {
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
            $requestWasValid = ExportHelper::validateExportRequest($this->Response(), $this->Request());

            if (!$requestWasValid) {
                return false;
            }

            $this->Response()->setHeader('Content-Type', 'text/html');
            $this->Response()->setBody('request looks ok');
            return true;
        } catch (Exception $e) {
            $this->Response()->setHeader('Content-Type', 'text/html');
            $this->Response()->setBody($e->getMessage());
            $this->Response()->setHttpResponseCode(500);
        }
    }

    /**
     * The API is available at /eight-select/exportStatus
     */
    public function exportStatusAction()
    {
        try {
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
            $requestWasValid = ExportHelper::validateExportRequest($this->Response(), $this->Request());

            if (!$requestWasValid) {
                return false;
            }

            $this->Response()->setHeader('Content-Type', 'text/html');
            $this->Response()->setBody('request looks ok');
            return true;
        } catch (Exception $e) {
            $this->Response()->setHeader('Content-Type', 'text/html');
            $this->Response()->setBody($e->getMessage());
            $this->Response()->setHttpResponseCode(500);
        }
    }
}
