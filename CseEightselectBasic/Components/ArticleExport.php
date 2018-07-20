<?php
namespace CseEightselectBasic\Components;

use League\Csv\Writer;
use CseEightselectBasic\Components\RunCronOnce;
use CseEightselectBasic\Components\FeedLogger;
use CseEightselectBasic\Components\ConfigValidator;
use CseEightselectBasic\Components\Export;

class ArticleExport extends Export
{
    const STORAGE = 'files/8select/';

    const CRON_NAME = '8select_article_export';

    const FEED_TYPE = 'product_feed';

    /**
     * @var array
     */
    protected $fields = [
        'sku',
        'mastersku',
        'status',
        'ean',
        'model',
        'name1',
        'name2',
        'kategorie1',
        'kategorie2',
        'kategorie3',
        'streich_preis',
        'angebots_preis',
        'groesse',
        'marke',
        'bereich',
        'rubrik',
        'abteilung',
        'kiko',
        'typ',
        'farbe',
        'farbspektrum',
        'absatzhoehe',
        'muster',
        'aermellaenge',
        'kragenform',
        'obermaterial',
        'passform',
        'schnitt',
        'waschung',
        'stil',
        'sportart',
        'detail',
        'auspraegung',
        'baukasten',
        'eigenschaft',
        'fuellmenge',
        'funktion',
        'gruppe',
        'material',
        'saison',
        'serie',
        'verschluss',
        'produkt_url',
        'bilder',
        'beschreibung',
        'beschreibung1',
        'beschreibung2',
        'sonstiges',
    ];

    public function __construct() {
        $this->header = $this->fields;
    }
}
