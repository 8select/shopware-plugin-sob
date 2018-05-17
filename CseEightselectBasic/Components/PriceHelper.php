<?php
namespace CseEightselectBasic\Components;

class PriceHelper
{
    /**
     * @param $article
     * @param $field
     * @return float
     */
    public static function getGrossPrice($article, $field)
    {
        $tax = $article['tax'];
        $price = $article[$field];

        // if streich_preis isn't set, use angebots_preis
        if ($price == 0) {
            $price = $article['angebots_preis'];
        }
        $price = $price + $price * $tax / 100;

        return number_format($price, 2, '.', '');
    }
}
