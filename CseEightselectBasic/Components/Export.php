<?php

namespace CseEightselectBasic\Components;

use CseEightselectBasic\Services\Dependencies\Provider;
use CseEightselectBasic\Services\Export\ExportInterface;
use CseEightselectBasic\Services\Export\Helper\Mapper;

abstract class Export implements ExportInterface
{
    /**
     * @var string[]
     */
    protected $fields = [];

    /**
     * @var Provider
     */
    protected $provider;

    /**
     * @var Mapper
     */
    protected $mapper;

    public function __construct()
    {
        $container = Shopware()->Container();
        $this->provider = $container->get('cse_eightselect_basic.dependencies.provider');
        $this->mapper = $container->get('cse_eightselect_basic.export.helper.mapper');
    }

    /**
     * @param bool $isDeltaExport
     * @return int
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

        $count = Shopware()->Db()->query($sql)->fetchColumn();

        return intval($count);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param bool $isDeltaOffset
     * @return array
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
    }

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
                IF (
                    s_articles.active && s_articles_details.active,
                    1,
                    0
                ) as active,
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
        $articles = Shopware()->Db()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        return $articles;
    }
}
