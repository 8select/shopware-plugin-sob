<?php

use CseEightselectBasic\Components\ArticleExport;
use CseEightselectBasic\Components\Export;
use CseEightselectBasic\Components\PropertyExport;
use CseEightselectBasic\Services\Request\AuthException;
use CseEightselectBasic\Services\Request\NotAuthorizedException;
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
     * The API is available at /cse-eightselect-basic/products.
     */
    public function productsAction()
    {
        try {
            $auth = $this->container->get('cse_eightselect_basic.request.auth');
            $auth->auth($this->Request());
        } catch (NotAuthorizedException $exception) {
            throw new \Enlight_Controller_Exception('hide export', 404);
        } catch (AuthException $exception) {
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
            $this->Response()->setHeader('Content-Type', 'application/json');
            $this->Response()->setHttpResponseCode($exception->getCode());
            $body = $this->httpBodyFromException($exception, 'AUTH_ERROR');
            $this->Response()->setBody($body);

            return;
        }

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Response()->setHeader('Content-Type', 'application/json');

        try {
            $limit = $this->Request()->getParam('limit', 50);
            $offset = $this->Request()->getParam('offset', 0);
            $format = $this->Request()->getParam('format', 'status');

            $export = $this->createExport($format);

            $responseData = $export->getProducts($limit, $offset);

            return $this->Response()->setBody(json_encode($responseData));
        } catch (\Exception $exception) {
            $this->Response()->setHttpResponseCode($exception->getCode());
            $body = $this->httpBodyFromException($exception, 'GENERAL_ERROR');
            $this->Response()->setBody($body);

            return;
        }
    }

    /**
     * @return Export
     */
    private function createExport($format)
    {
        switch ($format) {
            case 'product':
                return new ArticleExport();
                break;
            case 'property':
                return new PropertyExport(false);
                break;
            case 'status':
            default:
                return $this->container->get('cse_eightselect_basic.export.status_export');
        }
    }

    /**
     * @param \Exception $exception
     * @param string     $error
     *
     * @return string
     */
    private function httpBodyFromException($exception, $error)
    {
        return json_encode(
            [
                'error' => $error,
                'message' => $exception->getMessage(),
            ]
        );
    }

    public function getWhitelistedCSRFActions()
    {
        return [
            'products',
        ];
    }
}
