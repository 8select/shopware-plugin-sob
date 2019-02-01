<?php

namespace CseEightselectBasic\Services\Config;

use Doctrine\DBAL\Connection;

class Config
{
    const OPTION_EXPORT_TYPE = 'export_type';
    const OPTION_EXPORT_TYPE_VALUE_DELTA = 'delta_export';
    const OPTION_EXPORT_TYPE_VALUE_FULL = 'full_export';

    const TABLE_NAME = '8s_plugin_cse_config';

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
                `option` varchar(32) NOT NULL,
                `value` varchar(32) NOT NULL,
                PRIMARY KEY (`option`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
        ";

        $this->connection->executeUpdate($sql);
    }

    public function uninstall()
    {
        $sql = "DROP TABLE IF EXISTS `" . self::TABLE_NAME . "`;";
        $this->connection->executeUpdate($sql);
    }

    public function getOption($option)
    {
        $sql = "SELECT value FROM " . self::TABLE_NAME . " WHERE `option` = :option";
        return $this->connection->fetchColumn($sql, ['option' => $option]);
    }

    public function setOption($option, $value)
    {
        $sql = "REPLACE INTO " . self::TABLE_NAME . " (`option`, `value`) VALUES (:option, :value)";
        $this->connection->executeUpdate(
            $sql,
            ['option' => $option, 'value' => $value]
        );
    }
}
