<?php

class Shopware_Controllers_Frontend_EightSelect extends Enlight_Controller_Action
{

    /**
     * available at exampleshop.url/eight-select/cart
     */
    public function cartAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Response()->setBody(
            json_encode(['basket' => Shopware()->Modules()->Basket()->sGetBasket(), 'success' => true])
        );
        Shopware()->Plugins()->Controller()->Json()->setPadding();
    }

}
