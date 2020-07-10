<?php

namespace CseEightselectBasic\Services\Export\Helper;

use CseEightselectBasic\Services\Dependencies\Provider;
use Shopware\Components\DependencyInjection\Container;

class ProductImages
{
    /**
     * @var \sArticles
     */
    private $articles;

    /**
     * @param Container $container
     * @param Provider $provider
     */
    public function __construct(
        Container $container,
        Provider $provider
    ) {
        $container->set('shop', $provider->getShopWithActiveCSE());
        $this->articles = Shopware()->Modules()->sArticles();
    }

    /**
     * @param int $articleId
     * @param string $ordernumber
     * @param boolean $asArray
     * @return string
     */
    public function getImageUrls($articleId, $ordernumber, $asArray = false)
    {
        $imageUrls = [];
        $cover = $this->articles->sGetArticlePictures($articleId, true, null, $ordernumber);
        $imageUrls[] = $this->getUrlOfBiggestValidImage($cover['src']);

        $images = $this->articles->sGetArticlePictures($articleId, false, null, $ordernumber);
        foreach ($images as $image) {
            $imageUrls[] = $this->getUrlOfBiggestValidImage($image['src']);
        }

        if ($asArray === false) {
            return implode('|', $imageUrls);
        }

        return $imageUrls;
    }

    // for some SW configurations 'original' is null :-(
    private function getUrlOfBiggestValidImage($images) {
        $imageUrl = $images['original'];
        if (is_null($imageUrl) || $imageUrl === '') {
            unset($images['original']);
            $imageUrl = end($images);
        }

        return $imageUrl;
    }
}
