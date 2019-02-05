<?php

namespace CseEightselectBasic\Components;

class PropertyExport extends Export
{
    /**
     * @var array
     */
    protected $fields = [
        'prop_sku' => 'sku',
        'prop_isInStock' => 'status',
        'prop_parentSku' => 'mastersku',
        'prop_ean' => 'ean',
        'prop_model' => 'model',
        'prop_name' => 'name1',
        'prop_discountPrice' => 'angebots_preis',
        'prop_retailPrice' => 'streich_preis',
        'prop_size' => 'groesse',
        'prop_brand' => 'marke',
        'prop_color' => 'farbe',
        'prop_url' => 'produkt_url',
        'prop_description' => 'beschreibung',
        'images' => 'bilder',
    ];
}
