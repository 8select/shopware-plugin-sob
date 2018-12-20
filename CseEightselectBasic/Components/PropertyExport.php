<?php
namespace CseEightselectBasic\Components;

use CseEightselectBasic\Components\Export;

class PropertyExport extends Export
{
    const FEED_TYPE = 'property_feed';

    const CRON_NAME = '8select_property_export';

    /**
     * @var array
     */
    private $fieldMappingComplete = [
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

    /**
     * @var array
     */
    private $fieldMappingPriceAndStock = [
        'prop_sku' => 'sku',
        'prop_isInStock' => 'status',
        'prop_discountPrice' => 'angebots_preis',
        'prop_retailPrice' => 'streich_preis',
    ];

    public function __construct()
    {
        $fieldMapping = $this->fieldMappingPriceAndStock;
        if ($this->isDeltaExport()) {
            $fieldMapping = $this->fieldMappingComplete;
        }

        $this->header = array_keys($fieldMapping);
        $this->fields = array_values($fieldMapping);

        parent::__construct();
    }

    /**
     * @param string $mapping
     * @param int $offset
     * @param int $limit
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return array
     */
    protected function getArticles($mapping, $offset, $limit)
    {
        if ($this->isDeltaExport()) {
            return parent::getArticles($mapping, $offset, $limit);
        }

        $sqlTemplate = 'SELECT
                    s_articles.laststock AS laststock,
                    s_articles_prices.price AS angebots_preis,
                    s_articles_prices.pseudoprice AS streich_preis,
                    s_articles_details.active AS active,
                    s_articles_details.instock AS instock,
                    s_articles_details.ordernumber as sku,
                    s_core_tax.tax AS tax
                FROM s_articles_details
                    INNER JOIN s_articles ON s_articles.id = s_articles_details.articleID
                    INNER JOIN s_articles_prices ON s_articles_prices.articledetailsID = s_articles_details.id AND s_articles_prices.from = 1 AND s_articles_prices.pricegroup = "EK"
                    INNER JOIN s_core_tax ON s_core_tax.id = s_articles.taxID
                    INNER JOIN (
                        SELECT articleID
                        FROM s_articles_categories_ro
                        WHERE categoryID = %s
                        GROUP BY articleID
                    ) categoryConstraint ON categoryConstraint.articleID = s_articles_details.articleId;";
                LIMIT %d OFFSET %d';

        $sql = sprintf($sqlTemplate, $limit, $offset);

        if (getenv('ES_DEBUG')) {
            echo \PHP_EOL . 'SQL' . \PHP_EOL;
            echo $sql . \PHP_EOL;
        }

        $articles = Shopware()->Db()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        return $articles;
    }
}
