<?php

/**
 * Class Shopware_Controllers_Frontend_EightSelect
 * @category    Shopware
 * @package     Shopware_Plugins
 * @subpackage  EightSelect
 * @author      Onedrop GmbH & Co KG
 */
class Shopware_Controllers_Frontend_EightSelect extends Enlight_Controller_Action
{

    /**
     * Provides the cart of the current user as JSON API.
     * The API is available at /eight-select/cart
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

}
