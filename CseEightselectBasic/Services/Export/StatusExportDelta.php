<?php

namespace CseEightselectBasic\Services\Export;

use Doctrine\DBAL\Connection;

class StatusExportDelta
{
    const TABLE_NAME = 'cse_eightselect_basic_export_status_export_delta';

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

    public function install()
    {
        $this->uninstall();
        $sql = "
            CREATE TABLE `" . self::TABLE_NAME . "` (
                `s_articles_details_id` int(11) NOT NULL,
                `prop_sku` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
                `prop_discountPrice` double NOT NULL,
                `prop_retailPrice` double NOT NULL,
                `prop_isInStock` tinyint(4) NOT NULL,
                PRIMARY KEY (`s_articles_details_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";

        $this->connection->executeUpdate($sql);
    }

    public function uninstall()
    {
        $sql = "DROP TABLE IF EXISTS `" . self::TABLE_NAME . "`;";
        $this->connection->executeUpdate($sql);
    }

    /**
     * @param array $products
     */
    public function writeDeltaStatus($selectQuery, $params)
    {
        $sqlTemplate = "REPLACE INTO %s %s";
        $sql = sprintf(
            $sqlTemplate,
            self::TABLE_NAME,
            $selectQuery
        );
        $this->connection->executeUpdate($sql, $params);
    }
}
