<?php

/**
 * Class Shopware_Controllers_Frontend_CseEightselectBasic.
 *
 * @category    Shopware
 *
 * @author      Onedrop GmbH & Co KG
 */
use Shopware\Components\CSRFWhitelistAware;

class Shopware_Controllers_Frontend_CseEightselectBasic extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    /**
     * Provides the cart of the current user as JSON API.
     * The API is available at /cse-eightselect-basic/cart.
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
     * The API is available at /eight-select/products.
     */
    public function productsAction()
    {
        $responseErrorBody = $this->container->get('cse_eightselect_basic.response.error_body');

        try {
            $requestValidator = $this->container->get('cse_eightselect_basic.request.validator');
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
            $authorizationValidation = $requestValidator->checkAuthorizationForExport($this->Request());

            if (!$authorizationValidation['isAuthorized']) {
                $this->Response()->setHttpResponseCode(404);

                return;
            }

            $this->Response()->setHeader('Content-Type', 'application/json');
            $queryParamValidation = $requestValidator->checkQueryStringParamsForExport($this->Request());

            if (!$queryParamValidation['isValid']) {
                $errorBody = $responseErrorBody->getBadRequestBody($queryParamValidation['violations']);
                $this->Response()->setBody($errorBody);
                $this->Response()->setHttpResponseCode(400);

                return;
            }

            $configValidator = $this->container->get('cse_eightselect_basic.config.validator');
            $configValidation = $configValidator->validateConfig();

            if (!$configValidation['isValid']) {
                $errorBody = $responseErrorBody->getInternalServerErrorBody($configValidation['violations']);
                $this->Response()->setBody($errorBody);
                $this->Response()->setHttpResponseCode(500);

                return;
            }

            $this->Response()->setBody('return export here');
            $this->Response()->setHttpResponseCode(200);
        } catch (Exception $e) {
            $errorBody = $responseErrorBody->getInternalServerErrorBody($e->getMessage());
            $this->Response()->setHeader('Content-Type', 'application/json');
            $this->Response()->setBody();
            $this->Response()->setHttpResponseCode(500);
        }
    }

    public function getWhitelistedCSRFActions()
    {
        return [
            'products',
        ];
    }
}
