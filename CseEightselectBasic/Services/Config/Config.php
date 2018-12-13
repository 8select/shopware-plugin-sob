<?php

namespace CseEightselectBasic\Services\Config;

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
        $this->dropTable();
        $sql = "
            CREATE TABLE `:table_name` (
                `option` varchar(32) NOT NULL,
                `value` varchar(32) NOT NULL,
                PRIMARY KEY (`option`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
        ";

        $this->connection->executeUpdate($sql, ['table_name' => self::TABLE_NAME]);
    }

    public function uninstall()
    {
        $sql = "DROP TABLE IF EXISTS `:table_name`;";
        $this->connection->executeUpdate($sql, ['table_name' => self::TABLE_NAME]);
    }

    public function getOption($option)
    {
        $sql = "SELECT value from :table_name WHERE `option` = :option";
        return $this->connection->fetchColumn($sql, ['table_name' => self::TABLE_NAME, 'option' => $option]);
    }


    public function setOption($option, $value)
    {
        $sql = "REPLACE INTO :table_name VALUES(:option, :value)";
        $this->connection->executeUpdate(
            $sql,
            ['table_name' => self::TABLE_NAME, 'option' => $option, 'value' => $value]
        );
    }
}

