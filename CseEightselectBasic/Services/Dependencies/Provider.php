<?php

namespace CseEightselectBasic\Services\Dependencies;

use CseEightselectBasic\Compatibility\Shopware\Models\Shop\Repository as ShopRepository;
use CseEightselectBasic\Services\PluginConfig\PluginConfig as PluginConfigService;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Models\Shop\DetachedShop;
use Shopware\Models\Shop\Repository as LegacyShopRepository;
use Shopware\Models\Shop\Shop;

class Provider
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var PluginConfigService
     */
    private $pluginConfigService;

    /**
     * @param Container $container
     * @param PluginConfigService $pluginConfigService
     */
    public function __construct(
        Container $container,
        PluginConfigService $pluginConfigService
    ) {
        $this->container = $container;
        $this->pluginConfigService = $pluginConfigService;
    }

    /**
     * @return DetachedShop
     */
    public function getCurrentShop()
    {
        if ($this->hasShop()) {
            return $this->container->get('shop');
        }

        /** @var LegacyShopRepository $shopRepository */
        $shopRepository = $this->container->get('models')->getRepository(Shop::class);

        return $shopRepository->getActiveDefault();
    }

    /**
     * @return DetachedShop
     */
    public function getShopWithActiveCSE()
    {
        $cseShopId = $this->pluginConfigService->get('CseEightselectBasicActiveShopId');
        /** @var LegacyShopRepository $legacyShopRepository */
        $legacyShopRepository = $this->container->get('models')->getRepository(Shop::class);
        $shopRepository = new ShopRepository($legacyShopRepository);

        return $shopRepository->getById($cseShopId);
    }

    /**
     * @return bool
     */
    private function hasShop()
    {
        return $this->container->has('shop');
    }

    /**
     * @return string
     */
    public function getShopwareRelease()
    {
        if ($this->container->has('shopware.release')) {
            return $this->container->get('shopware.release')->getVersion();
        }

        return \Shopware::VERSION;
    }
}
