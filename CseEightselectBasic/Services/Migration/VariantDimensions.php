<?php

namespace CseEightselectBasic\Services\Migration;

use Doctrine\DBAL\Connection;

class VariantDimensions
{

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array
     */
    public function get()
    {
        $columnQuery = "SHOW COLUMNS FROM s_article_configurator_groups_attributes
            WHERE Field = 'od_cse_eightselect_basic_is_size';";
        $columns = $this->connection->fetchAll($columnQuery);
        $hasLegacyConfiguratorGroupAttribute = count($columns) === 1;

        if ($hasLegacyConfiguratorGroupAttribute) {
            $variantDimensionsQuery = '
            SELECT
                CONCAT("s_article_configurator_groups.id=", s_article_configurator_groups.id) as `name`,
                s_article_configurator_groups.name as label,
                IF(od_cse_eightselect_basic_is_size = 1, "SELECTED", "DESELECTED") as state
                FROM s_article_configurator_groups
                LEFT JOIN
                    s_article_configurator_groups_attributes
                ON s_article_configurator_groups.id = s_article_configurator_groups_attributes.groupID;';
        } else {
            $variantDimensionsQuery = '
                SELECT
                    CONCAT("s_article_configurator_groups.id=", s_article_configurator_groups.id) as `name`,
                    s_article_configurator_groups.name as label,
                    "DESELECTED" as state
                FROM s_article_configurator_groups;';
        }

        return $this->connection->fetchAll($variantDimensionsQuery);
    }
}
