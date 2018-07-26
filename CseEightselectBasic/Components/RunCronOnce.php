<?php
namespace CseEightselectBasic\Components;

class RunCronOnce
{
    const TABLE_NAME = '8s_cron_run_once';

    public static function runOnce($cronName) {
        if (self::isScheduled($cronName) || self::isRunning($cronName)) {
            $message = sprintf('%s nicht eingereiht, ist bereits in der Warteschleife.', $cronName);
            if (getenv('ES_DEBUG')) {
                echo $message . PHP_EOL;
            }
            return;
        }

        $connection = Shopware()->Container()->get('dbal_connection');
        $connection->insert(self::TABLE_NAME, ['cron_name' => $cronName, 'updated_at' => (new \DateTime())->format('Y-m-d H:i:s')]);
    }

    public static function isScheduled($cronName) {
        $sql = 'SELECT count(*) from ' . self::TABLE_NAME . ' WHERE running = 0 AND cron_name = "' . $cronName . '"';
        $scheduled = Shopware()->Db()->query($sql)->fetchColumn();

        return $scheduled !== '0';
    }

    public static function isRunning($cronName) {
        $sql = 'SELECT count(*) from ' . self::TABLE_NAME . ' WHERE running = 1 AND cron_name = "' . $cronName . '"';
        $running = Shopware()->Db()->query($sql)->fetchColumn();

        return $running !== '0';
    }

    public static function runCron($cronName) {
        $sql = 'UPDATE ' . self::TABLE_NAME . ' SET running = 1 WHERE cron_name = "' . $cronName . '"';
        Shopware()->Db()->query($sql);
    }

    public static function finishCron($cronName)
    {
        $sql = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE cron_name = "' . $cronName . '"';
        if (getenv('ES_DEBUG')) {
            echo $sql . \PHP_EOL;
        }
        Shopware()->Db()->query($sql);
    }

    public static function updateProgress($cronName, $progress) {
        $sql = 'UPDATE ' . self::TABLE_NAME . ' SET progress = ' . $progress . ' WHERE cron_name = "' . $cronName . '"';
        Shopware()->Db()->query($sql);
    }

    public static function getProgress($cronName) {
        $connection = Shopware()->Container()->get('dbal_connection');
        $queryBuilder = $connection->createQueryBuilder();
        $statement = $queryBuilder->select('progress')->from(self::TABLE_NAME)->where('cron_name = "' . $cronName . '"')->execute();
        if ($statement->rowCount()) {
            $progress = $statement->fetchColumn() ?: 0;
        } else {
            $progress = false;
        }

        return $progress;
    }

    public static function createTable() {
        $sqls = [
            'DROP TABLE IF EXISTS `' . self::TABLE_NAME . '`;',
            'CREATE TABLE `' . self::TABLE_NAME . '` (
                `cron_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `running` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `progress` tinyint(3) unsigned NOT NULL DEFAULT 0,
                PRIMARY KEY (`cron_name`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
        ];

        foreach ($sqls as $sql) {
            Shopware()->Db()->query($sql);
        }
    }

    public static function deleteTable() {
        $sql = 'DROP TABLE IF EXISTS `' . self::TABLE_NAME . '`;';
        Shopware()->Db()->query($sql);
    }
}
