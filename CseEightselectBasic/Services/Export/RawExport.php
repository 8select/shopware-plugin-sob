<?php

namespace CseEightselectBasic\Services\Export;

use CseEightselectBasic\Services\Dependencies\Provider;
use CseEightselectBasic\Services\Export\ExportInterface;
use CseEightselectBasic\Services\Export\RawExportMapper;
use Doctrine\DBAL\Connection;

class RawExport implements ExportInterface
{

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Provider $container
     * @param Connection $connection
     */
    public function __construct(Provider $provider, Connection $connection)
    {
        $this->provider = $provider;
        $this->connection = $connection;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param bool $isDeltaExport = true
     * @return array
     */
    public function getProducts($limit, $offset, $isDeltaExport = true)
    {
        $articleIds = $this->getArticleIds($limit, $offset);

        $rootData = $this->getRootData($articleIds);
        $configuratorGroups = $this->getConfiguratorGroups($articleIds);
        $filterOptions = $this->getFilterOptions($articleIds);
        $categories = $this->getCategories($articleIds);

        $products = [];
        foreach ($rootData as $product) {
            $sku = $product['sku'];
            $products[] = array_merge(
                $product,
                $configuratorGroups[$sku] ? $configuratorGroups[$sku] : [],
                $filterOptions[$sku] ? $filterOptions[$sku] : [],
                $categories[$sku] ? $categories[$sku] : []
            );
        }

        return $products;
    }

    private function getArticleIds($limit, $offset)
    {
        $sqlTemplate = "
            SELECT
                s_articles_details.id
            FROM
                s_articles_details
            INNER JOIN (
                SELECT
                    articleID
                FROM
                    s_articles_categories_ro
                WHERE
                    categoryID = %s
                GROUP BY
                    articleID
            ) categoryConstraint ON categoryConstraint.articleID = s_articles_details.articleID
            LIMIT %d OFFSET %d;
        ";
        $activeShop = $this->provider->getShopWithActiveCSE();
        $sql = sprintf($sqlTemplate, $activeShop->getCategory()->getId(), $limit, $offset);
        $articlesIds = $this->connection->fetchAll($sql);

        $mapper = function ($row) {
            return intval($row['id']);
        };

        return array_map($mapper, $articlesIds);
    }

    /**
     * @param array $articleIds
     * @return array
     */
    private function getRootData($articleIds)
    {
        $sql = "SELECT
                s_articles_details.ordernumber as `sku`,
                s_articles_details.ordernumber as `s_articles_details.ordernumber`,
                parentArticle.ordernumber as `parentArticle.ordernumber`,
                s_articles_details.suppliernumber as `s_articles_details.suppliernumber`,
                s_articles.name as `s_articles.name`,
                s_articles_supplier.name as `s_articles_supplier.name`,
                s_articles_details.additionaltext as `s_articles_details.additionaltext`,
                s_articles_details.weight as `s_articles_details.weight`,
                s_articles_details.width as `s_articles_details.width`,
                s_articles_details.height as `s_articles_details.height`,
                s_articles_details.length as `s_articles_details.length`,
                s_articles_details.ean as `s_articles_details.ean`,
                s_core_units.unit as `s_core_units.unit`,
                s_articles_details.purchaseunit as `s_articles_details.purchaseunit`,
                s_articles_details.referenceunit as `s_articles_details.referenceunit`,
                s_articles_details.packunit as `s_articles_details.packunit`,
                s_articles_details.releasedate as `s_articles_details.releasedate`,
                s_articles_details.shippingfree as `s_articles_details.shippingfree`,
                s_articles_details.shippingtime as `s_articles_details.shippingtime`,
                s_articles.active as `s_articles.active`,
                s_articles_details.active as `s_articles_details.active`,
                s_articles.laststock as `s_articles.laststock`,
                s_articles_details.instock as `s_articles_details.instock`,
                CAST(
                    s_articles_prices.price * (100 + s_core_tax.tax) AS DECIMAL(10,0)
                )
                as `s_articles_prices.price`,
                CAST(
                    IF(
                        s_articles_prices.pseudoprice = 0,
                        s_articles_prices.price,
                        s_articles_prices.pseudoprice
                    ) * (100 + s_core_tax.tax) AS DECIMAL(10,0)
                ) as `s_articles_prices.pseudoprice`,
                IF (
                    s_articles.active &&
                    s_articles_details.active &&
                    (!s_articles.laststock || s_articles_details.instock > 0),
                    1,
                    0
                ) as `s_articles_details.isInStock`,
                s_articles.metaTitle as `s_articles.metaTitle`,
                s_articles.keywords as `s_articles.keywords`
            FROM
                s_articles_details
            INNER JOIN
                s_articles_details AS parentArticle ON parentArticle.articleID = s_articles_details.articleID AND parentArticle.kind = 1
            INNER JOIN
                s_articles ON s_articles.id = s_articles_details.articleID
            LEFT JOIN s_articles_prices
                ON s_articles_prices.articledetailsID = s_articles_details.id
                AND s_articles_prices.from = 1
                AND s_articles_prices.pricegroup = 'EK'
            LEFT JOIN s_core_tax
                ON s_core_tax.id = s_articles.taxID
            LEFT JOIN
                s_core_units ON s_core_units.id = s_articles_details.unitID
            LEFT JOIN
                s_articles_supplier ON s_articles_supplier.id = s_articles.supplierID
            WHERE
                s_articles_details.id IN (?);
        ";

        $articles = $this->connection->fetchAll(
            $sql,
            array($articleIds),
            array(Connection::PARAM_INT_ARRAY)
        );

        $mapper = new RawExportMapper();

        return array_map(array($mapper, 'map'), $articles);
    }

    /**
     * @param array $articleIds
     * @return array
     */
    private function getConfiguratorGroups($articleIds)
    {
        $sql = "SELECT
                s_articles_details.ordernumber as `sku`,
                s_article_configurator_groups.id as detailSlugSuffix,
                s_article_configurator_groups.name as detailLabel,
                s_article_configurator_options.name as detailValue
            FROM
                s_articles_details
            INNER JOIN
                s_article_configurator_option_relations on s_article_configurator_option_relations.article_id = s_articles_details.id
            INNER JOIN
                s_article_configurator_options on s_article_configurator_options.id = s_article_configurator_option_relations.option_id
            INNER JOIN
                s_article_configurator_groups on s_article_configurator_groups.id = s_article_configurator_options.group_id
            WHERE
                s_articles_details.id IN (?);
        ";

        $configuratorGroups = $this->connection->fetchAll(
            $sql,
            array($articleIds),
            array(Connection::PARAM_INT_ARRAY)
        );

        return $this->mergeBySku($configuratorGroups, 's_article_configurator_groups.id=', true);
    }

    /**
     * @param array $articleIds
     * @return array
     */
    private function getFilterOptions($articleIds)
    {
        $sql = "SELECT
                s_articles_details.ordernumber as `sku`,
                s_filter_options.id as `detailSlugSuffix`,
                s_filter_options.name as `detailLabel`,
                s_filter_values.value as `detailValue`
            FROM
                s_articles_details
            INNER JOIN
                s_filter_articles on s_filter_articles.articleID = s_articles_details.articleID
            INNER JOIN
                s_filter_values on s_filter_values.id = s_filter_articles.valueID
            INNER JOIN
                s_filter_options on s_filter_options.id = s_filter_values.optionID
            WHERE
                s_articles_details.id IN (?);
        ";

        $filterOptions = $this->connection->fetchAll(
            $sql,
            array($articleIds),
            array(Connection::PARAM_INT_ARRAY)
        );

        return $this->mergeBySku($filterOptions, 's_filter_options.id=');
    }

    /**
     * @param array $articleIds
     * @return array
     */
    private function getCategories($articleIds)
    {
        $categoryNamesByCategoryId = $this->getCategoryNamesByCategoryId($articleIds);
        $categoryPathsListsBySku = $this->getCategoryPathsListsBySku($articleIds);

        $categoryPathsStrings = [];
        foreach ($categoryPathsListsBySku as $sku => $categoryPathsLists) {
            $strings = [];

            foreach ($categoryPathsLists as $categoryPathsList) {
                $string = '';
                $isFirst = true;
                foreach ($categoryPathsList as $categoryId) {
                    if (!$isFirst) {
                        $string .= ' > ';
                    }
                    $string .= $categoryNamesByCategoryId[$categoryId];
                    $strings[] = $string;
                    $isFirst = false;
                }
            }

            $categoryPathsStrings[$sku] = [
                's_categories' => [
                    'label' => 'Kategorie',
                    'value' => array_values(array_unique($strings)),
                ],
            ];
        }

        return $categoryPathsStrings;
    }

    private function getCategoryPathsListsBySku($articleIds)
    {
        $sql = "SELECT
                s_articles_details.ordernumber as `sku`,
                CONCAT(s_articles_categories_ro.categoryID, deepestCategory.path) as `path`
            FROM
                s_articles_details
            INNER JOIN
                s_articles_categories_ro on s_articles_categories_ro.articleID = s_articles_details.articleID
            INNER JOIN
                s_categories as deepestCategory on deepestCategory.id = s_articles_categories_ro.parentCategoryID AND s_articles_categories_ro.parentCategoryID = s_articles_categories_ro.categoryID
            WHERE
                s_articles_details.id IN (?);
        ";

        $categoryPaths = $this->connection->fetchAll(
            $sql,
            array($articleIds),
            array(Connection::PARAM_INT_ARRAY)
        );

        $categoryPathsListsBySku = [];
        foreach ($categoryPaths as $categoryPath) {
            $categoryPathList = explode('|', trim($categoryPath['path'], '|'));
            $categoryPathLists = [];
            if (array_key_exists($categoryPath['sku'], $categoryPathsListsBySku)) {
                $categoryPathLists = $categoryPathsListsBySku[$categoryPath['sku']];
            }
            $categoryPathLists[] = array_reverse($categoryPathList);
            $categoryPathsListsBySku[$categoryPath['sku']] = $categoryPathLists;
        }

        return $categoryPathsListsBySku;
    }

    private function getCategoryNamesByCategoryId($articleIds)
    {
        $sql = "SELECT
                s_categories.id,
                s_categories.description as `name`
            FROM
                s_articles_details
            INNER JOIN
                s_articles_categories_ro on s_articles_categories_ro.articleID = s_articles_details.articleID
            INNER JOIN
                s_categories on s_categories.id = s_articles_categories_ro.categoryID
            WHERE
                s_articles_details.id IN (?)
            GROUP BY  s_categories.id;
        ";

        $categoryNames = $this->connection->fetchAll(
            $sql,
            array($articleIds),
            array(Connection::PARAM_INT_ARRAY)
        );

        $categoryNamesById = [];
        foreach ($categoryNames as $categoryName) {
            $categoryNamesById[$categoryName['id']] = $categoryName['name'];
        }

        return $categoryNamesById;
    }

    private function mergeBySku($details, $detailSlugPrefix, $isVariantDetail = false)
    {
        $detailsPerArticle = [];
        foreach ($details as $detail) {
            $detailsOfArticle = [];
            if (array_key_exists($detail['sku'], $detailsPerArticle)) {
                $detailsOfArticle = $detailsPerArticle[$detail['sku']];
            }

            $detailOfArticle = [
                'label' => $detail['detailLabel'],
            ];
            if ($isVariantDetail) {
                $detailOfArticle['isVariantDetail'] = true;
            }
            $detailSlug = $detailSlugPrefix . $detail['detailSlugSuffix'];
            if (array_key_exists($detailSlug, $detailsOfArticle)) {
                $detailOfArticle = $detailsOfArticle[$detailSlug];
            }

            if (array_key_exists('value', $detailOfArticle) && !is_array($detailOfArticle['value'])) {
                $detailOfArticle['value'] = [$detailOfArticle['value']];
            }
            if (array_key_exists('value', $detailOfArticle) && is_array($detailOfArticle['value'])) {
                $detailOfArticle['value'][] = $detail['detailValue'];
            }
            if (!array_key_exists('value', $detailOfArticle)) {
                $detailOfArticle['value'] = $detail['detailValue'];
            }

            $detailsOfArticle[$detailSlug] = $detailOfArticle;
            $detailsPerArticle[$detail['sku']] = $detailsOfArticle;
        }

        return $detailsPerArticle;
    }

    public function getTotal($isDeltaExport = true)
    {
        $sqlTemplate = "
            SELECT
                COUNT(s_articles_details.id)
            FROM
                s_articles_details
            INNER JOIN (
                SELECT
                    articleID
                FROM
                    s_articles_categories_ro
                WHERE
                    categoryID = %s
                GROUP BY
                    articleID
            ) categoryConstraint ON categoryConstraint.articleID = s_articles_details.articleID;
        ";
        $activeShop = $this->provider->getShopWithActiveCSE();
        $sql = sprintf($sqlTemplate, $activeShop->getCategory()->getId());

        $count = $this->connection->fetchColumn($sql);

        return intval($count);
    }
}
