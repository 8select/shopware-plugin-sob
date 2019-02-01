<?php

namespace CseEightselectBasic\Setup\Updates;

use CseEightselectBasic\Services\Export\StatusExportDelta;
use CseEightselectBasic\Setup\SetupInterface;
use Doctrine\DBAL\Connection;

class Update_2_0_0 implements SetupInterface
{

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $exportPath;

    /**
     * @param Connection $connection
     * @param string $exportPath
     */
    public function __construct(Connection $connection, $exportPath)
    {
        $this->connection = $connection;
        $this->exportPath = $exportPath;
    }

    public function execute()
    {
        $this->deleteRunCronOnceTable();
        $this->removeExportDir();
        $this->removeExportCron();
        $this->removeExportOnceCron();
        $this->removePropertyCron();
        $this->removePropertyOnceCron();
        $this->removeQuickUpdateCron();
        $this->removeQuickUpdateOnceCron();
        $this->createStatusExportDeltaTable();
    }

    private function removeExportDir()
    {
        $this->rrmdir($this->exportPath);
    }

    private function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object === '.' || $object === '..') {
                    continue;
                }

                if (is_dir($object)) {
                    rrmdir($dir);
                } else {
                    unlink(sprintf('%s/%s', $dir, $object));
                }
            }
            rmdir($dir);
        }
    }

    private function deleteRunCronOnceTable()
    {
        $sql = 'DROP TABLE IF EXISTS `8s_cron_run_once`;';
        $this->connection->query($sql);
    }

    private function removeExportCron()
    {
        executeQuery(
            'DELETE FROM s_crontab WHERE `action` = ?',
            ['Shopware_CronJob_CseEightselectBasicArticleExport']
        );
    }

    private function removeExportOnceCron()
    {
        $this->connection->executeQuery(
            'DELETE FROM s_crontab WHERE `action` = ?',
            ['Shopware_CronJob_CseEightselectBasicArticleExportOnce']
        );
    }

    private function removePropertyCron()
    {
        $this->connection->executeQuery(
            'DELETE FROM s_crontab WHERE `action` = ?',
            ['Shopware_CronJob_CseEightselectBasicPropertyExport']
        );
    }

    private function removePropertyOnceCron()
    {
        $this->connection->executeQuery(
            'DELETE FROM s_crontab WHERE `action` = ?',
            ['Shopware_CronJob_CseEightselectBasicPropertyExportOnce']
        );
    }

    private function removeQuickUpdateCron()
    {
        $this->connection->executeQuery(
            'DELETE FROM s_crontab WHERE `action` = ?',
            ['Shopware_CronJob_CseEightselectBasicQuickUpdate']
        );
    }

    private function removeQuickUpdateOnceCron()
    {
        $this->connection->executeQuery(
            'DELETE FROM s_crontab WHERE `action` = ?',
            ['Shopware_CronJob_CseEightselectBasicQuickUpdateOnce']
        );
    }

    private function createStatusExportDeltaTable()
    {
        $statusExportDelta = new StatusExportDelta($this->connection);
        $statusExportDelta->install();
    }
}
