<?php

namespace CseEightselectBasic\Services\Export\Helper;

use Shopware\Models\Shop\Shop;

class ShopUrl
{
    /**
     * @param Shop $shop
     * @return string
     */
    public function getUrl(Shop $shop)
    {
        $url = sprintf(
            '%s://%s/%s/%s',
            $this->getScheme($shop),
            $this->getHost($shop),
            $this->getBaseUrl($shop),
            $this->getBasePath($shop)
        );

        return trim($url, '/');
    }

    /**
     * @param Shop $shop
     * @return string
     */
    private function getScheme(Shop $shop)
    {
        // this method was removed in Shopware 5.4
        if (method_exists($shop, 'getAlwaysSecure')) {
            return $shop->getAlwaysSecure() ? 'https' : 'http';
        }

        return $shop->getSecure() ? 'https' : 'http';
    }

    /**
     * @param Shop $shop
     * @return string
     */
    private function getHost(Shop $shop)
    {
        // this method was removed in Shopware 5.4
        if (method_exists($shop, 'getAlwaysSecure') &&
            method_exists($shop, 'getSecureHost') &&
            $shop->getAlwaysSecure()
        ) {
            return trim($shop->getSecureHost(), '/');
        }

        return trim($shop->getHost(), '/');
    }

    /**
     * @param Shop $shop
     * @return string
     */
    private function getBaseUrl(Shop $shop)
    {
        return trim($shop->getBaseUrl(), '/');
    }

    /**
     * @param Shop $shop
     * @return string
     */
    private function getBasePath(Shop $shop)
    {
        return trim($shop->getBasePath(), '/');
    }
}
