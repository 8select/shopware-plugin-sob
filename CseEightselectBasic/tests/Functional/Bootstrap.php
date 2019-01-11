<?php

use Shopware\Kernel;
use Shopware\Models\Shop\Shop;

require __DIR__.'/../../../../../autoload.php';

class CseEightselectBasicTestKernel extends Kernel
{
    public static function start()
    {
        $kernel = new self(getenv('SHOPWARE_ENV') ?: 'testing', true);
        $kernel->boot();
        $container = $kernel->getContainer();
        $container->get('plugins')->Core()->ErrorHandler()->registerErrorHandler(E_ALL | E_STRICT);

        $repository = $container->get('models')->getRepository(Shop::class);
        $shop = $repository->getActiveDefault();
        $shop->registerResources();

        if (!self::assertPlugin('CseEightselectBasic')) {
            throw new \RuntimeException('Plugin CseEightselectBasic must be installed and activated.');
        }
    }

    /**0
     * @param string $name
     *
     * @return bool
     */
    private static function assertPlugin($name)
    {
        $sql = 'SELECT 1 FROM s_core_plugins WHERE name = ? AND active = 1';

        return (bool) Shopware()->Container()->get('dbal_connection')->fetchColumn($sql, [$name]);
    }
}

CseEightselectBasicTestKernel::start();
