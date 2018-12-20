<?php

namespace CseEightselectBasic\Services\Export\Helper;

use CseEightselectBasic\Services\Dependencies\Provider;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\Router;

class ProductUrl
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @param Provider $container
     * @param \Shopware_Components_Config $config
     * @param Router $router
     */
    public function __construct(
        Provider $provider,
        \Shopware_Components_Config $config,
        Router $router
    ) {
        $activeShop = $provider->getShopWithActiveCSE();
        $context = Context::createFromShop($activeShop, $config);
        $router->setContext($context);
        $this->router = $router;
    }

    /**
     * @param  int $articleId
     * @param  string $ordernumber
     * @param  string $name
     * @throws \Exception
     * @return string
     */
    public function getUrl($articleId, $ordernumber, $name)
    {
        $assembleParams = [
            'module' => 'frontend',
            'sViewport' => 'detail',
            'sArticle' => $articleId,
            'number' => $ordernumber,
            'title' => $name,
        ];

        return $this->router->assemble($assembleParams);
    }
}
