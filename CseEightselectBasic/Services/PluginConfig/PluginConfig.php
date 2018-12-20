<?php

namespace CseEightselectBasic\Services\PluginConfig;

use Shopware\Components\ConfigWriter;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Models\Shop\DetachedShop;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;

class PluginConfig
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var DetachedShop
     */
    private $currentShop;

    /**
     * ConfigWriter
     */
    private $configWriter;

    /**
     * @var array
     */
    private $pluginConfig;

    /**
     * @param Container $container
     * @param ConfigReader $configReader
     * @param ConfigWriter $configWriter
     * @param string $pluginName
     */
    public function __construct(
        Container $container,
        ConfigReader $configReader,
        ConfigWriter $configWriter,
        $pluginName
    ) {
        $this->container = $container;
        $this->currentShop = $this->getCurrentShop();
        $this->pluginConfig = $configReader->getByPluginName($pluginName);
        $this->configWriter = $configWriter;
    }

    /**
     * @param string $key ConfigKey
     * @return mixed
     */
    public function get($key)
    {
        return $this->pluginConfig[$key];
    }

    /**
     * @return bool
     */
    public function isCseActive()
    {
        return $this->get('CseEightselectBasicPluginActive');
    }

    /**
     * @return bool
     */
    public function isCseActiveForCurrentShop()
    {
        $isActive = $this->get('CseEightselectBasicPluginActive');
        $isActiveForCurrentShop = $this->currentShop->getId() === $this->get('CseEightselectBasicActiveShopId');

        return $isActive && $isActiveForCurrentShop;
    }

    /**
     * @return DetachedShop
     */
    private function getCurrentShop()
    {
        if ($this->container->has('shop')) {
            return $this->container->get('shop');
        }

        /** @var ShopRepository $shopRepository */
        $shopRepository = $this->container->get('models')->getRepository(Shop::class);

        return $shopRepository->getActiveDefault();
    }

    public function setDefaults()
    {
        /** @var ShopRepository $shopRepository */
        $shopRepository = $this->container->get('models')->getRepository(Shop::class);
        $defaultShop = $shopRepository->getDefault();
        $this->configWriter->save('CseEightselectBasicActiveShopId', $defaultShop->getId());
    }
}
