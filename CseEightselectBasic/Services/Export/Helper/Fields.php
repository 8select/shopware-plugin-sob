<?php

namespace CseEightselectBasic\Services\Export\Helper;

class Fields
{
    const FIELD_TYPES_ROOT_FIELDS = 'ROOT_FIELDS';
    const FIELD_TYPES_FILTER_OPTIONS_FIELDS = 'FILTER_OPTIONS_FIELDS';
    const FIELD_TYPES_CATEGORIES_FIELDS = 'CATEGORIES_FIELDS';
    const FIELD_TYPES_CONFIGURATOR_GROUPS_FIELDS = 'CONFIGURATOR_GROUPS_FIELDS';
    const FIELD_TYPES_ATTRIBUTES_FIELDS = 'ATTRIBUTES_FIELDS';

    const ROOT_FIELDS = [
        'id',
        'parentId',
        'articleID',
        'sku',
        'images',
        'url',
        's_articles_details.ordernumber',
        'parentArticle.ordernumber',
        's_articles_details.suppliernumber',
        's_articles.name',
        's_articles_supplier.name',
        's_articles_details.additionaltext',
        's_articles_details.weight',
        's_articles_details.width',
        's_articles_details.height',
        's_articles_details.length',
        's_articles_details.ean',
        's_core_units.unit',
        's_articles_details.purchaseunit',
        's_articles_details.referenceunit',
        's_articles_details.packunit',
        's_articles_details.releasedate',
        's_articles_details.shippingfree',
        's_articles_details.shippingtime',
        's_articles.active',
        's_articles_details.active',
        's_articles.laststock',
        's_articles_details.instock',
        's_articles_prices.price',
        's_articles_prices.pseudoprice',
        's_articles_details.isInStock',
        's_articles.metaTitle',
        's_articles.keywords',
        's_articles.description',
        's_articles.description_long',
    ];

    const FILTER_OPTIONS_FIELDS = [
        's_filter_options',
    ];

    const CATEGORIES_FIELDS = [
        's_categories',
    ];

    const CONFIGURATOR_GROUPS_FIELDS = [
        's_article_configurator_groups',
    ];

    const ATTRIBUTES_FIELDS = [
        's_articles_attributes',
    ];

    /**
     * @param array $fields
     * @param string $type
     * @return bool
     */
    public function onlyContainsFieldsOfType($fields, $type)
    {
        $fieldsToDiff = constant('self::' . $type);
        $diff = array_diff($fields, $fieldsToDiff);

        return count($diff) === 0;
    }

    /**
     * @param array $fields
     * @param string $type
     * @return bool
     */
    public function containsFieldOfType($fields, $type)
    {
        $fieldsToDiff = constant('self::' . $type);
        $diff = array_intersect($fields, $fieldsToDiff);

        return count($diff) > 0;
    }
}
