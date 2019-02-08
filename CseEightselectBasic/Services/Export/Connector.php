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
            'base_url' => rtrim('__SHOP_CONNECTOR_URL__', '/'),
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
        $content = $this->getConnectDetails();
        $this->guzzleClient->put('/shops', $content);
    }

    /**
     * @return array
     */
    public function getConnectDetails()
    {
        return [
            'json' => [
                'tenant' => [
                    'feedId' => $this->pluginConfig->get('CseEightselectBasicFeedId'),
                    'id' => $this->pluginConfig->get('CseEightselectBasicApiId'),
                ],
                'shop' => [
                    'url' => $this->provider->getShopUrl(),
                    'software' => 'Shopware',
                    'version' => $this->provider->getShopwareRelease(),
                ],
                'plugin' => [
                    'version' => '__VERSION__',
                    'config' => $this->pluginConfig->getAll(),
                ],
                'api' => [
                    'products' => $this->provider->getShopUrl(true) . '/cse-eightselect-basic/products',
                ],
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
        $this->guzzleClient->delete($path);
    }
}
