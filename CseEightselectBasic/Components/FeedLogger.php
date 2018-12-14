<?php

namespace CseEightselectBasic\Components;

class FeedLogger
{
    const TABLE_NAME = '8s_feeds';

    /**
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public static function deleteTable()
    {
        $sql = 'DROP TABLE IF EXISTS `'.self::TABLE_NAME.'`;';
        Shopware()->Db()->query($sql);
    }
}
