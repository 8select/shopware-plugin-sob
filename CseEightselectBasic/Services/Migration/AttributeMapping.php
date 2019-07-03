<?php

namespace CseEightselectBasic\Services\Migration;

use CseEightselectBasic\Services\Export\Attributes;
use Doctrine\DBAL\Connection;

class AttributeMapping
{

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array
     */
    private $attributeLabelByName;

    /**
     * @param Connection $connection
     * @param Attributes $attributeService
     */
    public function __construct(Connection $connection, Attributes $attributeService)
    {
        $this->connection = $connection;
        $attributes = $attributeService->get();
        foreach ($attributes as $attribute) {
            $this->attributeLabelByName[$attribute['name']] = $attribute['label'];
        }
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->getTransformedLegacyAttributeMapping();
    }

    private function getTransformedLegacyAttributeMapping()
    {
        $sql = 'SELECT eightselectAttribute as attributeKey, shopwareAttribute as attributeName
        FROM 8s_attribute_mapping
        WHERE shopwareAttribute != "-"
        AND shopwareAttribute != ""
        AND shopwareAttribute IS NOT NULL;';

        $mapping = [];
        foreach ($this->connection->fetchAll($sql) as $mappingRow) {
            $attributeKey = $mappingRow['attributeKey'];
            $mapping[$attributeKey] = [];
            $attributeNames = explode(',', $mappingRow['attributeName']);
            foreach ($attributeNames as $attributeName) {
                $mapping[$attributeKey][] = [
                    'name' => $attributeName,
                    'label' => $this->mapNameToLabel($attributeName, $attributeKey),
                ];
            }
        }

        return $mapping;
    }

    /**
     * @param $name string
     * @param $key string
     * @return string
     */
    private function mapNameToLabel($name, $key)
    {
        if (array_key_exists($name, $this->attributeLabelByName)) {
            return $this->attributeLabelByName[$name];
        }

        return ucfirst($key);
    }
}
