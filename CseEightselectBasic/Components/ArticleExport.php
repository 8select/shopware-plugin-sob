<?php

namespace CseEightselectBasic\Components;

class ArticleExport extends Export
{
    const FEED_NAME = '8select_article_export';

    const FEED_TYPE = 'product_feed';

    /**
     * @var array
     */
    protected $fields = [
        'sku' => 'sku',
        'mastersku' => 'mastersku',
        'status' => 'status',
        'ean' => 'ean',
        'model' => 'model',
        'name1' => 'name1',
        'name2' => 'name2',
        'kategorie1' => 'kategorie1',
        'kategorie2' => 'kategorie2',
        'kategorie3' => 'kategorie3',
        'streich_preis' => 'streich_preis',
        'angebots_preis' => 'angebots_preis',
        'groesse' => 'groesse',
        'marke' => 'marke',
        'bereich' => 'bereich',
        'rubrik' => 'rubrik',
        'abteilung' => 'abteilung',
        'kiko' => 'kiko',
        'typ' => 'typ',
        'farbe' => 'farbe',
        'farbspektrum' => 'farbspektrum',
        'absatzhoehe' => 'absatzhoehe',
        'muster' => 'muster',
        'aermellaenge' => 'aermellaenge',
        'kragenform' => 'kragenform',
        'obermaterial' => 'obermaterial',
        'passform' => 'passform',
        'schnitt' => 'schnitt',
        'waschung' => 'waschung',
        'stil' => 'stil',
        'sportart' => 'sportart',
        'detail' => 'detail',
        'auspraegung' => 'auspraegung',
        'baukasten' => 'baukasten',
        'eigenschaft' => 'eigenschaft',
        'fuellmenge' => 'fuellmenge',
        'funktion' => 'funktion',
        'gruppe' => 'gruppe',
        'material' => 'material',
        'saison' => 'saison',
        'serie' => 'serie',
        'verschluss' => 'verschluss',
        'produkt_url' => 'produkt_url',
        'bilder' => 'bilder',
        'beschreibung' => 'beschreibung',
        'beschreibung1' => 'beschreibung1',
        'beschreibung2' => 'beschreibung2',
        'sonstiges' => 'sonstiges',
    ];

    public function __construct()
    {
        $this->header = $this->fields;
        parent::__construct();
    }
}
