<?php

namespace CseEightselectBasic\Services\Export;

use CseEightselectBasic\Services\Dependencies\Provider;
use CseEightselectBasic\Services\PluginConfig\PluginConfig;
use GuzzleHttp\ClientInterface;
use Shopware\Components\HttpClient\GuzzleFactory;

class Connector
{

    /**
     * @var ClientInterface
     */
    private $guzzleClient;

    /**
     * @var PluginConfig
     */
    private $pluginConfig;

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var bool
     */
    private $canConnect = false;

    /**
     * @param GuzzleFactory $guzzleFactory
     * @param PluginConfig $pluginConfig
     * @param Provider $container
     */
    public function __construct(GuzzleFactory $guzzleFactory, PluginConfig $pluginConfig, Provider $provider)
    {
        $this->guzzleClient = $guzzleFactory->createClient([
            'defaults' => [
                'timeout' => 5,
                'connect_timeout' => 5,
                'headers' => [
                    '8select-com-tid' => $pluginConfig->get('CseEightselectBasicApiId'),
                    '8select-com-fid' => $pluginConfig->get('CseEightselectBasicFeedId'),
                ],
            ],
        ]);
        $this->pluginConfig = $pluginConfig;
        $this->provider = $provider;

        $this->canConnect = $pluginConfig->areCseCredentialsConfigured();
    }

    /**
     * @throws CseCredentialsMissingException
     */
    public function connect()
    {
        if ($this->canConnect === false) {
            throw new CseCredentialsMissingException();
        }
        $content = [
            'json' => $this->getConnectDetails(),
        ];

        $this->guzzleClient->put($this->getUrl('shops'), $content);
    }

    /**
     * @return array
     */
    public function getConnectDetails()
    {
        $shopUrl = $this->provider->getShopUrl(true);
        return [
            'tenant' => [
                'feedId' => $this->pluginConfig->get('CseEightselectBasicFeedId'),
                'id' => $this->pluginConfig->get('CseEightselectBasicApiId'),
            ],
            'shop' => [
                'url' => $shopUrl,
                'software' => 'Shopware',
                'version' => $this->provider->getShopwareRelease(),
            ],
            'plugin' => [
                'version' => '__VERSION__',
                'config' => $this->pluginConfig->getAll(),
            ],
            'api' => [
                'attributes' => $shopUrl . '/cse-eightselect-basic/attributes',
                'products' => $shopUrl . '/cse-eightselect-basic/products',
                'variantDimensions' => $shopUrl . '/cse-eightselect-basic/variant-dimensions',
                'migrationAttributeMappings' => $shopUrl . '/cse-eightselect-basic/migration-attribute-mappings',
                'migrationVariantDimensions' => $shopUrl . '/cse-eightselect-basic/migration-variant-dimensions',
            ],
        ];
    }

    /**
     * @throws CseCredentialsMissingException
     */
    public function disconnect()
    {
        if ($this->canConnect === false) {
            throw new CseCredentialsMissingException();
        }
        $path = sprintf(
            '/shops/%s/%s',
            $this->pluginConfig->get('CseEightselectBasicApiId'),
            $this->pluginConfig->get('CseEightselectBasicFeedId')
        );
        $this->guzzleClient->delete($this->getUrl($path));
    }

    /**
     * @return string
     */
    private function getUrl($path)
    {
        return sprintf(
            '%s/%s',
            rtrim('__SHOP_CONNECTOR_URL__', '/'),
            ltrim($path, '/')
        );
    }
}
