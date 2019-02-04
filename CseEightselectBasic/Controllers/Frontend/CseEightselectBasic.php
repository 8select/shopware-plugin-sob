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
     * The API is available at /cse-eightselect-basic/connect
     */
    public function connectAction()
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
            $connector = $this->container->get('cse_eightselect_basic.export.connector');
            $connection = $connector->getConnectDetails();
            $this->Response()->setBody(json_encode($connection));

            return;
        } catch (\Exception $exception) {
            $this->Response()->setHttpResponseCode(500);
            $body = $this->httpBodyFromException($exception, 'GENERAL_ERROR');
            $this->Response()->setBody($body);

            return;
        }
    }

    /**
     * The API is available at /cse-eightselect-basic/products
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
            $configValidator = $this->container->get('cse_eightselect_basic.config.validator');
            $result = $configValidator->validateExportConfig();
            if ($result['isValid'] === false) {
                $this->Response()->setHttpResponseCode(500);
                $body = json_encode(
                    [
                        'error' => 'CONFIGURATION_ERROR',
                        'message' => $result['violations'],
                    ]
                );
                $this->Response()->setBody($body);

                return;
            }
        } catch (\Exception $exception) {
            $this->Response()->setHttpResponseCode(500);
            $body = $this->httpBodyFromException($exception, 'GENERAL_ERROR');
            $this->Response()->setBody($body);

            return;
        }

        try {
            $format = $this->Request()->getParam('format');
            $export = $this->createExport($format);
            if (!$format || !$export) {
                $this->Response()->setHttpResponseCode(204);
                return;
            }

            $limit = filter_var($this->Request()->getParam('limit', 50), FILTER_VALIDATE_INT);
            $offset = filter_var($this->Request()->getParam('offset', 0), FILTER_VALIDATE_INT);
            $isDeltaExport = filter_var($this->Request()->getParam('delta', true), FILTER_VALIDATE_BOOLEAN);

            $response = json_encode(
                [
                    'limit' => $limit,
                    'offset' => $offset,
                    'total' => $export->getTotal($isDeltaExport),
                    'data' => $export->getProducts($limit, $offset, $isDeltaExport),
                ]
            );

            return $this->Response()->setBody($response);
        } catch (\Exception $exception) {
            $this->Response()->setHttpResponseCode(500);
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
            case 'etl':
                return new ArticleExport();
                break;
            case 'property':
                return new PropertyExport();
                break;
            case 'status':
                return $this->container->get('cse_eightselect_basic.export.status_export');
                break;
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
            'connect',
            'products',
        ];
    }
}
