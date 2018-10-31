<?php
namespace CseEightselectBasic\Components;

class Config
{
    const OPTION_EXPORT_TYPE = 'export_type';
    const OPTION_EXPORT_TYPE_VALUE_DELTA = 'delta_export';
    const OPTION_EXPORT_TYPE_VALUE_FULL = 'full_export';

    const TABLE_NAME = '8s_plugin_cse_config';

    public static function createTable()
    {
        self::dropTable();
        Shopware()->Db()->query('CREATE TABLE `' . self::TABLE_NAME . '` (
            `option` varchar(32) NOT NULL,
            `value` varchar(32) NOT NULL,
            PRIMARY KEY (`option`)
        ) COLLATE=\'utf8_unicode_ci\' ENGINE=InnoDB DEFAULT CHARSET=utf8;');
    }

    public static function dropTable()
    {
        Shopware()->Db()->query('DROP TABLE IF EXISTS `' . self::TABLE_NAME . '`;');
    }

    public static function getOption($option)
    {
        $sql = 'SELECT value from %s WHERE `option` = "%s"';
        $query = sprintf($sql, self::TABLE_NAME, $option);

        return Shopware()->Db()->query($query)->fetchColumn();
    }


    public static function setOption($option, $value)
    {
        $sql = 'REPLACE INTO %s VALUES("%s", "%s")';
        $query = sprintf($sql, self::TABLE_NAME, $option, $value);
        Shopware()->Db()->query($query);
    }
}
