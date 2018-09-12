<?php
namespace CseEightselectBasic\Components;

use League\Csv\Writer;
use CseEightselectBasic\Components\RunCronOnce;
use CseEightselectBasic\Components\Export;

class PropertyExport extends Export
{
    const STORAGE = 'files/8select/';

    const FEED_TYPE = 'property_feed';

    const CRON_NAME = '8select_property_export';

    /**
     * @var array
     */
    private $fieldMapping = [
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
        'images' => 'bilder'
    ];

    public function __construct() {
        $this->header = array_keys($this->fieldMapping);
        $this->fields = array_values($this->fieldMapping);
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return integer
     */
    protected function getNumArticles()
    {
        $sql = 'SELECT COUNT(DISTINCT s_articles_details_id) as count FROM 8s_articles_details_change_queue';
        $count = Shopware()->Db()->query($sql)->fetchColumn();

        return intval($count);
    }
}
