<?php

namespace CseEightselectBasic\Services\Export;

use CseEightselectBasic\Services\Export\Helper\ProductImages;
use CseEightselectBasic\Services\Export\Helper\ProductUrl;

class RawExportMapper
{

    /**
     * @var ProductUrl
     */
    private $urlHelper;

    /**
     * @var ProductImages
     */
    private $imageHelper;

    /**
     * @param ProductUrl $urlHelper
     * @param ProductImages $imageHelper
     */
    public function __construct(
        ProductUrl $urlHelper,
        ProductImages $imageHelper
    ) {
        $this->urlHelper = $urlHelper;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @var array
     */
    private $map = [
        's_articles_details.ordernumber' => 'Artikelnummer',
        's_articles_details.suppliernumber' => 'Herstellernummer',
        's_articles_details.additionaltext' => 'Zusätzlicher Text',
        's_articles_details.weight' => 'Gewicht',
        's_articles_details.width' => 'Breite',
        's_articles_details.height' => 'Höhe',
        's_articles_details.length' => 'Länge',
        's_articles_details.ean' => 'EAN',
        's_core_units.unit' => 'Maßeinheit',
        's_articles_details.purchaseunit' => 'Inhalt',
        's_articles_details.referenceunit' => 'Grundeinheit',
        's_articles_details.packunit' => 'Verpackungseinheit',
        's_articles_details.releasedate' => 'Erscheinungsdatum',
        's_articles_details.shippingfree' => 'Versandkostenfrei',
        's_articles_details.shippingtime' => 'Lieferzeit',
        'parentArticle.ordernumber' => 'Artikelnummer Hauptartikel',
        's_articles.active' => 'Aktiv für den Hauptartikel',
        's_articles_details.active' => 'Aktiv für die Variante',
        's_articles.laststock' => 'Abverkauf',
        's_articles_details.instock' => 'Lagerbestand',
        's_articles_prices.price' => 'Brutto-Preis',
        's_articles_prices.pseudoprice' => 'Pseudo Brutto-Preis',
        's_articles_details.isInStock' => 'Bestellbar',
        's_articles.name' => 'Artikel-Bezeichnung',
        's_articles.metaTitle' => 'Titel',
        's_articles.keywords' => 'Keywords',
        's_articles_supplier.name' => 'Hersteller',
    ];

    /**
     * @param array $product - ['slug' => 'detailValue']
     * @return array ['slug' => ['label' => 'detailLabel', 'value' => 'detailValue']]
     */
    public function map($product)
    {
        $mapped = [
            'id' => $product['id'],
            'sku' => $product['sku'],
            'url' => $this->urlHelper->getUrl($product['articleID'], $product['sku'], $product['s_articles.name']),
            'images' => $this->imageHelper->getImageUrls($product['articleID'], $product['sku'], true),
        ];
        foreach ($product as $slug => $detailValue) {
            if ($slug === 'articleID' || $slug === 'sku' || $slug === 'id') {
                continue;
            }

            $mapped[$slug] = [
                'label' => $this->getLabel($slug),
                'value' => $detailValue,
            ];
        }

        return $mapped;
    }

    /**
     * @param $slug string
     * @return string
     */
    private function getLabel($slug)
    {
        if (array_key_exists($slug, $this->map)) {
            return $this->map[$slug];
        }

        return $slug;
    }
}
