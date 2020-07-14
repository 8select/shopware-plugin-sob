<?php

use CseEightselectBasic\Services\Export\ExportInterface;
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
        $this->addPluginInfoHeaders();
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Response()->setHeader('Content-Type', 'application/json');
        $this->Response()->setBody(
            json_encode(['basket' => Shopware()->Modules()->Basket()->sGetBasket(), 'success' => true])
        );
        Shopware()->Plugins()->Controller()->Json()->setPadding();
    }

    public function validateAction()
    {
        $this->addPluginInfoHeaders();

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
            $result = $configValidator->validateConfig();
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

            $this->Response()->setHttpResponseCode(200);
            $body = json_encode(
                [
                    'message' => 'CONFIGURATION_VALID',
                ]
            );
            $this->Response()->setBody($body);
        } catch (\Exception $exception) {
            $this->Response()->setHttpResponseCode(500);
            $body = $this->httpBodyFromException($exception, 'GENERAL_ERROR');
            $this->Response()->setBody($body);

            return;
        }
    }

    /**
     * The API is available at /cse-eightselect-basic/connect
     */
    public function connectAction()
    {
        $this->addPluginInfoHeaders();

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
            $this->tryConnect($connector);
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

    private function tryConnect($connector)
    {
        try {
            $connector->connect();
        } catch (\Exception $ignore) {
        }
    }

    /**
     * The API is available at /cse-eightselect-basic/attributes
     */
    public function attributesAction()
    {
        $this->addPluginInfoHeaders();

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
            $attributesService = $this->container->get('cse_eightselect_basic.export.attributes');
            $attributes = $attributesService->get();

            $response = json_encode(
                [
                    'limit' => count($attributes),
                    'offset' => 0,
                    'total' => count($attributes),
                    'data' => $attributes,
                ]
            );

            $this->Response()->setBody($response);

            return;
        } catch (\Exception $exception) {
            $this->Response()->setHttpResponseCode(500);
            $body = $this->httpBodyFromException($exception, 'GENERAL_ERROR');
            $this->Response()->setBody($body);

            return;
        }
    }

    /**
     * The API is available at /cse-eightselect-basic/variant-dimensions
     */
    public function variantDimensionsAction()
    {
        $this->addPluginInfoHeaders();

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
            $limit = filter_var($this->Request()->getParam('limit', 1000), FILTER_VALIDATE_INT);
            $offset = filter_var($this->Request()->getParam('offset', 0), FILTER_VALIDATE_INT);
            $variantDimensionsService = $this->container->get('cse_eightselect_basic.export.variant_dimensions');
            $variantDimensions = $variantDimensionsService->get($limit, $offset);

            $response = json_encode(
                [
                    'limit' => $limit,
                    'offset' => $offset,
                    'total' => $variantDimensionsService->getTotal(),
                    'data' => $variantDimensions,
                ]
            );

            $this->Response()->setBody($response);

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
        $this->addPluginInfoHeaders();

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
            $format = $this->Request()->getParam('format', 'raw');
            $export = $this->createExport($format);
            if (!$format || !$export) {
                $this->Response()->setHttpResponseCode(204);
                return;
            }

            $limit = filter_var($this->Request()->getParam('limit', 50), FILTER_VALIDATE_INT);
            $offset = filter_var($this->Request()->getParam('offset', 0), FILTER_VALIDATE_INT);
            $isDeltaExport = filter_var($this->Request()->getParam('delta', false), FILTER_VALIDATE_BOOLEAN);
            $fields = $this->Request()->getParam('fields');
            $filter = $this->Request()->getParam('filter');
            Shopware()->Container()->get('pluginlogger')->info('incoming request', ['filter' => $filter]);
            $sku = $filter['sku'];

            $data = $export->getProducts($limit, $offset, $isDeltaExport, $fields, $sku);
            $response = json_encode(
                [
                    'limit' => $limit,
                    'offset' => $offset,
                    'total' => $export->getTotal($isDeltaExport, $sku),
                    'data' => $data,
                ]
            );

            if ($response === false) {
                $message = 'json error while exporting';
                $context = [
                    'error' => [
                        'json_last_error' => json_last_error(),
                        'json_last_error_msg' => json_last_error_msg(),
                    ],
                    'skus' => $this->extractSkus($data),
                ];
                $logger = $this->container->get('cse_eightselect_basic.setup.helpers.logger');
                $logger->log('export', [[
                    'message' => $message,
                    'context' => $context,
                ]], true);
            }

            return $this->Response()->setBody($response);
        } catch (\Exception $exception) {
            $logger = $this->container->get('cse_eightselect_basic.setup.helpers.logger');
            $context = [
                'exception' => [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTrace(),
                ],
            ];
            $logger->log('export', [[
                'message' => 'error while exporting',
                'context' => $context,
            ]], true);

            $this->Response()->setHttpResponseCode(500);
            $body = $this->httpBodyFromException($exception, 'GENERAL_ERROR');
            $this->Response()->setBody($body);

            return;
        }
    }

    /**
     * @param array $products
     * @return array
     */
    private function extractSkus($products)
    {
        $skus = [];
        foreach ($products as $product) {
            $skus[] = $product['s_articles_details.ordernumber']['value'];
        }

        return $skus;
    }

    /**
     * @return ExportInterface
     */
    private function createExport($format)
    {
        switch ($format) {
            case 'status':
                return $this->container->get('cse_eightselect_basic.export.status_export');
                break;
            case 'raw':
                return $this->container->get('cse_eightselect_basic.export.raw_export');
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
            'attributes',
            'variantDimensions',
            'validate',
        ];
    }

    /**
     * The API is available at /cse-eightselect-basic/migration-attribute-mappings
     */
    public function migrationAttributeMappingsAction()
    {
        $this->addPluginInfoHeaders();

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
            $attributeMappingService = $this->container->get('cse_eightselect_basic.migration.attribute_mapping');
            $attributeMapping = $attributeMappingService->get();

            $response = json_encode(
                [
                    'limit' => count($attributeMapping),
                    'offset' => 0,
                    'total' => count($attributeMapping),
                    'data' => $attributeMapping,
                ]
            );

            $this->Response()->setBody($response);

            return;
        } catch (\Exception $exception) {
            $this->Response()->setHttpResponseCode(500);
            $body = $this->httpBodyFromException($exception, 'GENERAL_ERROR');
            $this->Response()->setBody($body);

            return;
        }
    }

    /**
     * The API is available at /cse-eightselect-basic/migration-variant-dimensions
     */
    public function migrationVariantDimensionsAction()
    {
        $this->addPluginInfoHeaders();

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
            $variantDimensionsService = $this->container->get('cse_eightselect_basic.migration.variant_dimensions');
            $variantDimensions = $variantDimensionsService->get();

            $response = json_encode(
                [
                    'limit' => count($variantDimensions),
                    'offset' => 0,
                    'total' => count($variantDimensions),
                    'data' => $variantDimensions,
                ]
            );

            $this->Response()->setBody($response);

            return;
        } catch (\Exception $exception) {
            $this->Response()->setHttpResponseCode(500);
            $body = $this->httpBodyFromException($exception, 'GENERAL_ERROR');
            $this->Response()->setBody($body);

            return;
        }
    }

    private function addPluginInfoHeaders() {
        $this->Response()->setHeader('8select-com-plugin-version', '__VERSION__');
    }
}
