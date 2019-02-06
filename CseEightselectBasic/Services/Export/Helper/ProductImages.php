<?php

namespace CseEightselectBasic\Services\Export\Helper;

use CseEightselectBasic\Services\Dependencies\Provider;
use Shopware\Components\DependencyInjection\Container;

class ProductImages
{
    /**
     * @var \sExport
     */
    private $export;

    /**
     * @param Container $container
     * @param Provider $provider
     */
    public function __construct(
        Container $container,
        Provider $provider
    ) {
        $container->set('shop', $provider->getShopWithActiveCSE());
        $this->export = Shopware()->Modules()->Export();
    }

    /**
     * @param  int $articleId
     * @param  string $ordernumber
     * @return string
     */
    public function getImageUrls($articleId, $ordernumber)
    {
        return $this->export->sGetArticleImageLinks($articleId, $ordernumber, 'original', '|');
    }
}
