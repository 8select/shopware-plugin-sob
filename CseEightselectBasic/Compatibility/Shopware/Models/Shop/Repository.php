<?php

namespace CseEightselectBasic\Compatibility\Shopware\Models\Shop;

use Shopware\Models\Shop\DetachedShop;
use Shopware\Models\Shop\Repository as LegacyRepository;

class Repository
{

    /**
     * @var LegacyRepository $legacyRepository
     */
    private $legacyRepository;

    /**
     * @param LegacyRepository $legacyRepository
     */
    public function __construct(LegacyRepository $legacyRepository)
    {
        $this->legacyRepository = $legacyRepository;
    }

    /**
     * Shopware < 5.3.4 can not return inactive shops
     * https://github.com/shopware/shopware/pull/1248
     * @param int $id
     *
     * @return DetachedShop
     */
    public function getById($id)
    {
        if (method_exists($this->legacyRepository, 'getById')) {
            return $this->legacyRepository->getById($id);
        }

        $builder = $this->legacyRepository->getActiveQueryBuilder();
        $builder->where('shop.id=:shopId');
        $builder->setParameter('shopId', $id);
        $shop = $builder->getQuery()->getOneOrNullResult();

        if ($shop !== null) {
            $class = new \ReflectionClass($this->legacyRepository);
            $method = $class->getMethod('fixActive');
            $method->setAccessible(true);
            $shop = $method->invoke($this->legacyRepository, $shop);
        }

        return $shop;
    }

    public function __call($name, $arguments)
    {
        return $this->legacyRepository->$name($arguments);
    }
}
