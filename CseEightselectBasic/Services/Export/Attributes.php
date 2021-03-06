<?php

namespace CseEightselectBasic\Services\Export;

use CseEightselectBasic\Services\Export\VariantDimensions;
use Doctrine\DBAL\Connection;

class Attributes
{

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var VariantDimensions
     */
    private $variantDimensions;

    /**
     * @param Connection $connection
     * @param VariantDimensions $variantDimensions
     */
    public function __construct(Connection $connection, VariantDimensions $variantDimensions)
    {
        $this->connection = $connection;
        $this->variantDimensions = $variantDimensions;
    }

    /**
     * @return array
     */
    public function get()
    {
        return array_merge(
            $this->getFixedAttributes(),
            $this->variantDimensions->get(),
            $this->getFilterOptions(),
            $this->getAttributeConfiguration()
        );
    }

    private function getFixedAttributes()
    {
        return [
            ['name' => 's_articles.name', 'label' => 'Artikel-Bezeichnung'],
            ['name' => 's_articles.metaTitle', 'label' => 'Titel'],
            ['name' => 's_articles.description', 'label' => 'Kurzbeschreibung'],
            ['name' => 's_articles.description_long', 'label' => 'Beschreibung'],
            ['name' => 's_articles.keywords', 'label' => 'Keywords'],
            ['name' => 's_articles_details.additionaltext', 'label' => 'Zusätzlicher Text'],
            ['name' => 's_articles_details.weight', 'label' => 'Gewicht'],
            ['name' => 's_articles_details.width', 'label' => 'Breite'],
            ['name' => 's_articles_details.height', 'label' => 'Höhe'],
            ['name' => 's_articles_details.length', 'label' => 'Länge'],
            ['name' => 's_articles_details.ean', 'label' => 'EAN'],
            ['name' => 's_core_units.unit', 'label' => 'Maßeinheit'],
            ['name' => 's_categories', 'label' => 'Kategorie'],
            ['name' => 's_articles_supplier.name', 'label' => 'Hersteller'],
        ];
    }

    /**
     * @return array
     */
    private function getFilterOptions()
    {
        $sql = "
            SELECT
                s_filter_options.id as nameSuffix,
                s_filter_options.name as label
            FROM
                s_filter_options;
            ";

        $filterOptions = [];
        foreach ($this->connection->fetchAll($sql) as $filterOption) {
            $name = 's_filter_options.id=' . $filterOption['nameSuffix'];
            $filterOptions[] = [
                'name' => $name,
                'label' => $this->getNonEmpty($filterOption['label'], $name),
            ];
        }

        return $filterOptions;
    }

    /**
     * @return array
     */
    public function getAttributeConfiguration()
    {
        $attributeColumnsQuery = "SHOW COLUMNS FROM s_articles_attributes;";
        $attributeColumns = $this->connection->fetchAll($attributeColumnsQuery);
        $attributeColumnsIndexed = array_column($attributeColumns, 'Field', 'Field');

        $attributesQuery = "
            SELECT
                s_attribute_configuration.column_name as nameSuffix,
                s_attribute_configuration.label
            FROM
                s_attribute_configuration
            WHERE
                table_name = 's_articles_attributes';
            ";

        $attributes = [];
        foreach ($this->connection->fetchAll($attributesQuery) as $attribute) {
            if (!array_key_exists($attribute['nameSuffix'], $attributeColumnsIndexed)) {
                continue;
            }
            $name = 's_articles_attributes.' . $attribute['nameSuffix'];
            $attributes[] = [
                'name' => $name,
                'label' => $this->getNonEmpty($attribute['label'], $name),
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
