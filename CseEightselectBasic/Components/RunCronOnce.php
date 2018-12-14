<?php

namespace CseEightselectBasic\Components;

class RunCronOnce
{
    const TABLE_NAME = '8s_cron_run_once';

    public static function deleteTable()
    {
        $sql = 'DROP TABLE IF EXISTS `'.self::TABLE_NAME.'`;';
        Shopware()->Db()->query($sql);
    }
}
