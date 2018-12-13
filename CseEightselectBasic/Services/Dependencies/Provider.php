<?php

namespace CseEightselectBasic\Services\Dependencies;

use Doctrine\DBAL\Connection;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Models\Shop\DetachedShop;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;

class Provider
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var string
     */
    private $pluginName;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Container $container
     * @param ConfigReader $configReader
     * @param string $pluginName
     * @param Connection $connection
     */
    public function __construct(
        Container $container,
        ConfigReader $configReader,
        $pluginName,
        Connection $connection
    )
    {
        $this->container = $container;
        $this->configReader = $configReader;
        $this->pluginName = $pluginName;
        $this->connection = $connection;
    }

    /**
     * @return \Shopware_Components_Config
     */
    public function getPluginConfig()
    {
        return $this->configReader->getByPluginName($this->pluginName, $this->getShop());
    }

    /**
     * @return DetachedShop
     */
    public function getShop()
    {
        if ($this->hasShop()) {
            return $this->container->get('shop');
        }

        /** @var ShopRepository $shopRepository */
        $shopRepository = $this->container->get('models')->getRepository(Shop::class);

        return $shopRepository->getActiveDefault();
    }

    /**
     * @return bool
     */
    private function hasShop()
    {
        return $this->container->has('shop');
    }

    /**
     * @return Connection
     */
    public function getDbConnection()
    {
        return $this->connection;
    }

}
