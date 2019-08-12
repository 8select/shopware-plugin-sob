<?php

namespace CseEightselectBasic\Services\Export;

use CseEightselectBasic\Services\Dependencies\Provider;
use CseEightselectBasic\Services\Export\Attributes;
use CseEightselectBasic\Services\Export\ExportInterface;
use CseEightselectBasic\Services\Export\Helper\Fields;
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
     * @var Attributes
     */
    private $attributes;

    /**
     * @var RawExportMapper
     */
    private $mapper;

    /**
     * @var Fields
     */
    private $fields;

    /**
     * @param Provider $container
     * @param Connection $connection
     * @param Attributes $attributes
     * @param RawExportMapper $mapper
     * @param Fields $mapper
     */
    public function __construct(
        Provider $provider,
        Connection $connection,
        Attributes $attributes,
        RawExportMapper $mapper,
        Fields $fields
    ) {
        $this->provider = $provider;
        $this->connection = $connection;
        $this->attributes = $attributes;
        $this->mapper = $mapper;
        $this->fields = $fields;
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

    /**
     * @param int $limit
     * @param int $offset
     * @param bool $isDeltaExport = true
     * @param array $fields = null
     * @return array
     */
    public function getProducts($limit, $offset, $isDeltaExport = true, $fields = null)
    {
        $articleIds = $this->getArticleIds($limit, $offset);

        $rootData = $this->getRootData($articleIds, $fields);

        if (!is_null($fields) && $this->fields->onlyContainsFieldsOfType($fields, Fields::FIELD_TYPES_ROOT_FIELDS)) {
            return $rootData;
        }

        $configuratorGroups = [];
        if (is_null($fields) ||
            $this->fields->containsFieldOfType($fields, Fields::FIELD_TYPES_CONFIGURATOR_GROUPS_FIELDS)
        ) {
            $configuratorGroups = $this->getConfiguratorGroups($articleIds);
        }

        $filterOptions = [];
        if (is_null($fields) ||
            $this->fields->containsFieldOfType($fields, Fields::FIELD_TYPES_FILTER_OPTIONS_FIELDS)
        ) {
            $filterOptions = $this->getFilterOptions($articleIds);
        }

        $categories = [];
        if (is_null($fields) ||
            $this->fields->containsFieldOfType($fields, Fields::FIELD_TYPES_CATEGORIES_FIELDS)
        ) {
            $categories = $this->getCategories($articleIds);
        }

        $attributes = [];
        if (is_null($fields) ||
            $this->fields->containsFieldOfType($fields, Fields::FIELD_TYPES_ATTRIBUTES_FIELDS)
        ) {
            $attributes = $this->getAttributes($articleIds);
        }

        $products = [];
        foreach ($rootData as $product) {
            $sku = $product['s_articles_details.ordernumber']['value'];
            $products[] = array_merge(
                $product,
                $configuratorGroups[$sku] ? $configuratorGroups[$sku] : [],
                $filterOptions[$sku] ? $filterOptions[$sku] : [],
                $categories[$sku] ? $categories[$sku] : [],
                $attributes[$sku] ? $attributes[$sku] : []
            );
        }

        return $products;
    }

    /**
     * @param integer $limit
     * @param integer $offset
     */
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
     * @param array $fields = null
     * @return array
     */
    private function getRootData($articleIds, $fields = null)
    {
        $basicSelect = [
            's_articles_details.id as `s_articles_details.id`',
            's_articles_details.articleID as `s_articles_details.articleID`',
            's_articles_details.ordernumber as `s_articles_details.ordernumber`',
        ];

        $additionalSelect = [
            'url' => '"url" as `url`',
            'images' => '"images" as `images`',
            's_articles_details.ordernumber' => 's_articles_details.ordernumber as `s_articles_details.ordernumber`',
            's_articles_details.suppliernumber' => 's_articles_details.suppliernumber as `s_articles_details.suppliernumber`',
            's_articles.name' => 's_articles.name as `s_articles.name`',
            's_articles_supplier.name' => 's_articles_supplier.name as `s_articles_supplier.name`',
            's_articles_details.additionaltext' => 's_articles_details.additionaltext as `s_articles_details.additionaltext`',
            's_articles_details.weight' => 's_articles_details.weight as `s_articles_details.weight`',
            's_articles_details.width' => 's_articles_details.width as `s_articles_details.width`',
            's_articles_details.height' => 's_articles_details.height as `s_articles_details.height`',
            's_articles_details.length' => 's_articles_details.length as `s_articles_details.length`',
            's_articles_details.ean' => 's_articles_details.ean as `s_articles_details.ean`',
            's_core_units.unit' => 's_core_units.unit as `s_core_units.unit`',
            's_articles_details.purchaseunit' => 's_articles_details.purchaseunit as `s_articles_details.purchaseunit`',
            's_articles_details.referenceunit' => 's_articles_details.referenceunit as `s_articles_details.referenceunit`',
            's_articles_details.packunit' => 's_articles_details.packunit as `s_articles_details.packunit`',
            's_articles_details.releasedate' => 's_articles_details.releasedate as `s_articles_details.releasedate`',
            's_articles_details.shippingfree' => 's_articles_details.shippingfree as `s_articles_details.shippingfree`',
            's_articles_details.shippingtime' => 's_articles_details.shippingtime as `s_articles_details.shippingtime`',
            's_articles.active' => 's_articles.active as `s_articles.active`',
            's_articles_details.active' => 's_articles_details.active as `s_articles_details.active`',
            's_articles.laststock' => 's_articles.laststock as `s_articles.laststock`',
            's_articles_details.instock' => 's_articles_details.instock as `s_articles_details.instock`',

            's_articles_prices.price' => 'CAST(
 s_articles_prices.price * (100 + s_core_tax.tax) AS DECIMAL(10,0)
) as `s_articles_prices.price`',

            's_articles_prices.pseudoprice' => 'CAST(
 IF(
 s_articles_prices.pseudoprice = 0,
 s_articles_prices.price,
 s_articles_prices.pseudoprice
 ) * (100 + s_core_tax.tax) AS DECIMAL(10,0) ) as `s_articles_prices.pseudoprice`',

            's_articles_details.isInStock' => 'IF (
 s_articles.active &&
 s_articles_details.active &&
 (!s_articles.laststock || s_articles_details.instock > 0),
 1,
 0 ) as `s_articles_details.isInStock`',
            's_articles.metaTitle' => 's_articles.metaTitle as `s_articles.metaTitle`',
            's_articles.keywords' => 's_articles.keywords as `s_articles.keywords`',
            's_articles.description' => 's_articles.description as `s_articles.description`',
            's_articles.description_long' => 's_articles.description_long as `s_articles.description_long`',
        ];

        if (is_null($fields) || !is_array($fields)) {
            $filteredSelect = $additionalSelect;
        } else {
            $filteredSelect = array_intersect_key($additionalSelect, array_flip($fields));
        }
        $select = array_merge($basicSelect, $filteredSelect);

        $sqlTemplate = "SELECT
                %s
            FROM
                s_articles_details
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

        $sql = sprintf($sqlTemplate, implode(',', $select));
        $articles = $this->connection->fetchAll(
            $sql,
            array($articleIds),
            array(Connection::PARAM_INT_ARRAY)
        );

        return array_map(array($this->mapper, 'map'), $articles);
    }

    /**
     * @param array $articleIds
     * @return array
     */
    private function getConfiguratorGroups($articleIds)
    {
        $sql = "SELECT
                s_articles_details.ordernumber as `s_articles_details.ordernumber`,
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
                s_articles_details.ordernumber as `s_articles_details.ordernumber`,
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
                s_articles_details.ordernumber as `s_articles_details.ordernumber`,
                IFNULL(CONCAT(s_articles_categories_ro.categoryID, deepestCategory.path), deepestCategory.id) as `path`
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
            if (array_key_exists($categoryPath['s_articles_details.ordernumber'], $categoryPathsListsBySku)) {
                $categoryPathLists = $categoryPathsListsBySku[$categoryPath['s_articles_details.ordernumber']];
            }
            $categoryPathLists[] = array_reverse($categoryPathList);
            $categoryPathsListsBySku[$categoryPath['s_articles_details.ordernumber']] = $categoryPathLists;
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
            if (is_null($detail['detailValue']) || $detail['detailValue'] === '') {
                continue;
            }

            $detailsOfArticle = [];
            if (array_key_exists($detail['s_articles_details.ordernumber'], $detailsPerArticle)) {
                $detailsOfArticle = $detailsPerArticle[$detail['s_articles_details.ordernumber']];
            }

            $detailSlug = $detailSlugPrefix . $detail['detailSlugSuffix'];
            $detailOfArticle = [
                'label' => $this->getNonEmpty($detail['detailLabel'], $detailSlug),
            ];
            if ($isVariantDetail) {
                $detailOfArticle['isVariantDetail'] = true;
            }
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
            $detailsPerArticle[$detail['s_articles_details.ordernumber']] = $detailsOfArticle;
        }

        return $detailsPerArticle;
    }

    /**
     * @param array $articleIds
     * @return array
     */
    private function getAttributes($articleIds)
    {
        $attributeMeta = $this->attributes->getAttributeConfiguration();
        $attributeColumnsArray = [];
        $attributeMetaByName = [];
        foreach ($attributeMeta as $attributeCentiMeta) {
            $name = $attributeCentiMeta['name'];
            $attributeColumnsArray[] = $name . ' as "' . $name . '"';
            $attributeMetaByName[$name] = $attributeCentiMeta;
        }

        $attributeColumns = implode(', ', $attributeColumnsArray);

        $sqlTemplate = "SELECT
            s_articles_details.ordernumber as `s_articles_details.ordernumber`,
            %s
        FROM
            s_articles_details
        INNER JOIN
            s_articles_attributes on s_articles_attributes.articledetailsID = s_articles_details.id
        WHERE
            s_articles_details.id IN (?)
        ";

        $sql = sprintf($sqlTemplate, $attributeColumns);

        $attributes = $this->connection->fetchAll(
            $sql,
            array($articleIds),
            array(Connection::PARAM_INT_ARRAY)
        );

        $attributesPerArticle = [];
        foreach ($attributes as $attributesOfArticle) {
            $sku = $attributesOfArticle['s_articles_details.ordernumber'];
            $attributesPerArticle[$sku] = $this->transformAttribute($attributesOfArticle, $attributeMetaByName);
        }

        return $attributesPerArticle;
    }

    /**
     * @param array $attributesOfArticle
     * @param array $attributeMetaByName
     * @return array
     */
    private function transformAttribute($attributesOfArticle, $attributeMetaByName)
    {
        $attributes = [];
        foreach ($attributesOfArticle as $name => $value) {
            if (array_key_exists($name, $attributeMetaByName) === false) {
                continue;
            }

            if (is_null($value) || $value === '') {
                continue;
            }

            $attributes[$name] = [
                'label' => $attributeMetaByName[$name]['label'],
                'value' => $value,
            ];
        }

        return $attributes;
    }

    /**
     * @param string $label
     * @param string $name
     * @return string
     */
    private function getNonEmpty($label, $name)
    {
        return strlen($label) === 0 ? $name : $label;
    }
}
