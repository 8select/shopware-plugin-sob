<?php

namespace CseEightselectBasic\Setup\Helpers;

use CseEightselectBasic\Services\Dependencies\Provider;
use CseEightselectBasic\Services\PluginConfig\PluginConfig;
use GuzzleHttp\ClientInterface;
use Shopware\Components\HttpClient\GuzzleFactory;

class Logger
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
            ],
        ]);
        $this->pluginConfig = $pluginConfig;
        $this->provider = $provider;
    }

    /**
     * @param string $type
     * @param array $messages
     * @param bool $hasError
     */
    public function log($type, $messages, $hasError = false)
    {
        try {
            $content = $this->getMessageDefaults();
            $content['json']['type'] = $type;
            $content['json']['hasError'] = $hasError;
            $content['json']['messages'] = $messages;

            $this->guzzleClient->post('/logs', $content);
        } catch (\Exception $ignore) {
        }
    }

    /**
     * @return array
     */
    public function getMessageDefaults()
    {
        return [
            'json' => [
                'tenant' => [
                    'tid' => $this->pluginConfig->get('CseEightselectBasicApiId'),
                    'fid' => $this->pluginConfig->get('CseEightselectBasicFeedId'),
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
            ],
        ];
    }
}
