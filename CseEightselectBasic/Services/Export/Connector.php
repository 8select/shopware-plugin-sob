<?php

namespace CseEightselectBasic\Services\Export;

use CseEightselectBasic\Services\Dependencies\Provider;
use CseEightselectBasic\Services\Export\Helper\ShopUrl;
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
            'json' => [
                'tenant' => [
                    'tid' => $this->pluginConfig->get('CseEightselectBasicApiId'),
                    'fid' => $this->pluginConfig->get('CseEightselectBasicFeedId'),
                ],
                'shop' => [
                    'url' => $this->getShopUrl(),
                    'software' => 'Shopware',
                    'version' => $this->provider->getShopwareRelease(),
                ],
                'plugin' => [
                    'version' => '__VERSION__',
                ],
                'api' => [
                    'products' => $this->getShopUrl() . '/cse-eightselect-basic/products',
                ],
            ]];
        $this->guzzleClient->put('/shops', $content);
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
        $this->guzzleClient->delete();
    }

    /**
     * return string
     */
    private function getShopUrl()
    {
        $shop = $this->provider->getShopWithActiveCSE();
        if ($shop === null) {
            $shop = $this->provider->getCurrentShop();
        }

        $shopUrlHelper = new ShopUrl();
        return $shopUrlHelper->getUrl($shop);
    }
}
