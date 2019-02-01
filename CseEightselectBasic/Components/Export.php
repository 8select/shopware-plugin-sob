<?php

namespace CseEightselectBasic\Components;

use CseEightselectBasic\Services\Config\Config;
use CseEightselectBasic\Services\Config\Validator;
use CseEightselectBasic\Services\Dependencies\Provider;
use CseEightselectBasic\Services\Export\ExportInterface;

abstract class Export implements ExportInterface
{
    /**
     * @var int
     */
    private $currentProgress = 0;

    /**
     * @var string[]
     */
    protected $header = [];

    /**
     * @var string[]
     */
    protected $fields = [];

    /**
     * @var Provider
     */
    protected $provider;

    /**
     * @var Validator
     */
    protected $configValidator;

    /**
     * @var Config
     */
    protected $config;

    public function __construct()
    {
        $container = Shopware()->Container();
        $this->provider = $container->get('cse_eightselect_basic.dependencies.provider');
        $this->configValidator = $container->get('cse_eightselect_basic.config.validator');
        $this->config = $container->get('cse_eightselect_basic.config.config');
        $this->mapper = $container->get('cse_eightselect_basic.export.helper.mapper');
    }

    /**
     * @param bool $isDeltaExport
     */
    public function getTotal($isDeltaExport = true)
    {
        $sqlTemplate = "
            SELECT
                COUNT(s_articles_details.id)
            FROM s_articles_details
                INNER JOIN s_articles_details AS ad2 ON ad2.articleID = s_articles_details.articleID AND ad2.kind = 1
                INNER JOIN s_articles ON s_articles.id = s_articles_details.articleID
                LEFT JOIN s_articles_attributes ON s_articles_attributes.articledetailsID = s_articles_details.id
                INNER JOIN s_articles_prices ON s_articles_prices.articledetailsID = s_articles_details.id AND s_articles_prices.from = 1 AND s_articles_prices.pricegroup = 'EK'
                INNER JOIN s_articles_supplier ON s_articles_supplier.id = s_articles.supplierID
                INNER JOIN s_core_tax ON s_core_tax.id = s_articles.taxID
                INNER JOIN (
                    SELECT articleID
                    FROM s_articles_categories_ro
                    WHERE categoryID = %s
                    GROUP BY articleID
                ) categoryConstraint ON categoryConstraint.articleID = s_articles_details.articleID
            ";

        $activeShop = $this->provider->getShopWithActiveCSE();
        $sql = sprintf($sqlTemplate, $activeShop->getCategory()->getId());
        dump($sql);

        $count = Shopware()->Db()->query($sql)->fetchColumn();

        return intval($count);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param bool $isDeltaOffset
     */
    public function getProducts($limit, $offset, $isDeltaExport = true)
    {
        $attributeMapping = $this->getAttributeMapping();
        $articles = $this->getArticles($attributeMapping, $offset, $limit);

        $products = [];
        foreach ($articles as $article) {
            $products[] = $this->mapper->map($article, $this->fields);
        }

        return $products;

        // if ($this->canGenerateResponse() === false) {
        //     // @todo fehler ausgeben - kann nur passieren wenn etwas nicht korrekt konfiguriert ist
        //     return [
        //         'limit' => $limit,
        //         'offset' => $offset,
        //         'total' => 0,
        //         'data' => [],
        //     ];
        // }

        // $productData = $this->getDataBatch($limit, $offset);

        // $response = [
        //     'limit' => $limit,
        //     'offset' => $offset,
        //     'total' => $this->getNumArticles(),
        //     'data' => $productData,
        // ];

        // return $response;
    }

    // /**
    //  * @return array
    //  */
    // protected function canGenerateResponse()
    // {
    //     //@todo die API Antwort muss das hier enthalten, damit wir mitbekommen wenn was noch nicht richtig konfiguriert ist
    //     $validationResult = $this->configValidator->validateExportConfig();
    //     if ($validationResult['isValid'] === false) {
    //         $message = sprintf('%s nicht ausgeführt, da die Plugin Konfiguration ungültig ist.', static::FEED_NAME);
    //         Shopware()->PluginLogger()->warning($message);

    //         return false;
    //     }

    //     if ($this->getNumArticles() <= 0) {
    //         $message = sprintf('%s nicht ausgeführt, es wurden keine Produkte für Export gefunden.', static::FEED_NAME);
    //         Shopware()->PluginLogger()->info($message);
    //         return false;
    //     }

    //     return true;
    // }

    /**
     * @return string
     */
    private function getAttributeMapping()
    {
        $attributeMappingQuery = 'SELECT GROUP_CONCAT(CONCAT(shopwareAttribute," AS ",eightselectAttribute)) as resultMapping
        FROM 8s_attribute_mapping
        WHERE shopwareAttribute != "-"
        AND shopwareAttribute != ""
        AND shopwareAttribute IS NOT NULL
        AND shopwareAttribute NOT LIKE "%id=%"';

        return Shopware()->Db()->query($attributeMappingQuery)->fetch(\PDO::FETCH_ASSOC)['resultMapping'];
    }

    /**
     * @param string $mapping
     * @param int    $offset
     * @param int    $limit
     *
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     *
     * @return array
     */
    protected function getArticles($mapping, $offset, $limit)
    {
        $sqlTemplate = "
            SELECT %s,
                s_articles_details.articleID,
                s_articles.laststock AS laststock,
                s_articles_details.id as detailID,
                s_articles_prices.price AS angebots_preis,
                s_articles_prices.pseudoprice AS streich_preis,
                s_articles_details.active AS active,
                s_articles_details.instock AS instock,
                s_articles_supplier.name as marke,
                ad2.ordernumber as mastersku,
                s_articles_details.ordernumber as sku,
                s_core_tax.tax AS tax
            FROM s_articles_details
                INNER JOIN s_articles_details AS ad2 ON ad2.articleID = s_articles_details.articleID AND ad2.kind = 1
                INNER JOIN s_articles ON s_articles.id = s_articles_details.articleID
                LEFT JOIN s_articles_attributes ON s_articles_attributes.articledetailsID = s_articles_details.id
                INNER JOIN s_articles_prices ON s_articles_prices.articledetailsID = s_articles_details.id AND s_articles_prices.from = 1 AND s_articles_prices.pricegroup = 'EK'
                INNER JOIN s_articles_supplier ON s_articles_supplier.id = s_articles.supplierID
                INNER JOIN s_core_tax ON s_core_tax.id = s_articles.taxID
                INNER JOIN (
                    SELECT articleID
                    FROM s_articles_categories_ro
                    WHERE categoryID = %s
                    GROUP BY articleID
                ) categoryConstraint ON categoryConstraint.articleID = s_articles_details.articleID
            LIMIT %d OFFSET %d;
            ";

        $activeShop = $this->provider->getShopWithActiveCSE();
        $sql = sprintf($sqlTemplate, $mapping, $activeShop->getCategory()->getId(), $limit, $offset);
        dump($sql);
        $articles = Shopware()->Db()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        return $articles;
    }
}
