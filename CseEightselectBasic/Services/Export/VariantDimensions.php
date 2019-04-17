<?php

namespace CseEightselectBasic\Services\Export;

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
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get($limit = 1000, $offset = 0)
    {
        $dimenions = [];
        foreach ($this->getConfiguratorGroups($limit, $offset) as $dimension) {
            $dimenions[] = [
                'name' => 's_article_configurator_groups.id=' . $dimension['nameSuffix'],
                'label' => $dimension['label'],
            ];
        }

        return $dimenions;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return array
     */
    private function getConfiguratorGroups($limit, $offset)
    {
        $sqlTemplate = "
            SELECT
                s_article_configurator_groups.id as nameSuffix,
                s_article_configurator_groups.name as label
            FROM
                s_article_configurator_groups
            LIMIT %d OFFSET %d;
            ";
        $sql = sprintf($sqlTemplate, $limit, $offset);

        return $this->connection->fetchAll($sql);
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        $sql = "
            SELECT
                COUNT(s_article_configurator_groups.id)
            FROM
                s_article_configurator_groups;
            ";

        $count = $this->connection->fetchColumn($sql);

        return intval($count);
    }
}
