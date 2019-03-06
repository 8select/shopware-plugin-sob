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
        $this->removeChangeQueue();
        $this->removeConfig();
        $this->removeFeedLog();
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
        $this->connection->exec($sql);
    }

    private function removeExportCron()
    {
        $this->connection->executeUpdate(
            'DELETE FROM s_crontab WHERE `action` = ?',
            ['Shopware_CronJob_CseEightselectBasicArticleExport']
        );
    }

    private function removeExportOnceCron()
    {
        $this->connection->executeUpdate(
            'DELETE FROM s_crontab WHERE `action` = ?',
            ['Shopware_CronJob_CseEightselectBasicArticleExportOnce']
        );
    }

    private function removePropertyCron()
    {
        $this->connection->executeUpdate(
            'DELETE FROM s_crontab WHERE `action` = ?',
            ['Shopware_CronJob_CseEightselectBasicPropertyExport']
        );
    }

    private function removePropertyOnceCron()
    {
        $this->connection->executeUpdate(
            'DELETE FROM s_crontab WHERE `action` = ?',
            ['Shopware_CronJob_CseEightselectBasicPropertyExportOnce']
        );
    }

    private function removeQuickUpdateCron()
    {
        $this->connection->executeUpdate(
            'DELETE FROM s_crontab WHERE `action` = ?',
            ['Shopware_CronJob_CseEightselectBasicQuickUpdate']
        );
    }

    private function removeQuickUpdateOnceCron()
    {
        $this->connection->executeUpdate(
            'DELETE FROM s_crontab WHERE `action` = ?',
            ['Shopware_CronJob_CseEightselectBasicQuickUpdateOnce']
        );
    }

    private function removeChangeQueue()
    {
        if ($this->areTriggersInstalled() === false) {
            return;
        }

        $this->tryDropTriggers();

        $this->connection->exec('DROP TABLE IF EXISTS `8s_articles_details_change_queue`');
    }

    private function areTriggersInstalled()
    {
        $ourTriggersAreInstalled = false;

        try {
            $triggers = [
                '8s_articles_change_queue_writer',
                '8s_articles_details_change_queue_writer',
                '8s_articles_img_change_queue_writer',
                '8s_s_articles_prices_change_queue_writer',
                '8s_s_articles_attributes_change_queue_writer',
                '8s_s_article_configurator_option_relations_change_queue_writer',
                '8s_s_article_img_mappings_change_queue_writer',
                '8s_s_article_img_mapping_rules_change_queue_writer',
                '8s_articles_supplier_change_queue_writer',
            ];

            $query = "SHOW TRIGGERS WHERE `Trigger` LIKE '8s_%';";
            $result = Shopware()->Db()->query($query)->fetchAll();
            foreach ($result as $trigger) {
                if (array_search($trigger['Trigger'], $triggers) !== false) {
                    $ourTriggersAreInstalled = true;
                    break;
                }
            }

            return $ourTriggersAreInstalled;
        } catch (\Exception $exception) {
            $template = 'could not check for installed 8select DB trigger due to exception: %s';
            $message = sprintf($template, $exception->getMessage());
            $context = [
                'exception' => [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTrace(),
                ],
            ];
            Shopware()->Container()->get('pluginlogger')->warning($message, $context);

            return $ourTriggersAreInstalled;
        }
    }

    private function tryDropTriggers()
    {
        try {
            $triggerQueries = [
                'DROP TRIGGER IF EXISTS `8s_articles_change_queue_writer`',
                'DROP TRIGGER IF EXISTS `8s_articles_details_change_queue_writer`',
                'DROP TRIGGER IF EXISTS `8s_articles_img_change_queue_writer`',
                'DROP TRIGGER IF EXISTS `8s_s_articles_prices_change_queue_writer`',
                'DROP TRIGGER IF EXISTS `8s_s_articles_attributes_change_queue_writer`',
                'DROP TRIGGER IF EXISTS `8s_s_article_configurator_option_relations_change_queue_writer`',
                'DROP TRIGGER IF EXISTS `8s_s_article_img_mappings_change_queue_writer`',
                'DROP TRIGGER IF EXISTS `8s_s_article_img_mapping_rules_change_queue_writer`',
                'DROP TRIGGER IF EXISTS `8s_articles_supplier_change_queue_writer`',
            ];

            foreach ($triggerQueries as $query) {
                $this->connection->exec($query);
            }
        } catch (\Exception $exception) {
            $template = 'unable to remove installed 8select DB triggers due to exception: %s';
            $message = sprintf($template, $exception->getMessage());
            $context = [
                'exception' => [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTrace(),
                ],
            ];
            Shopware()->Container()->get('pluginlogger')->error($message, $context);

            throw new \Exception('Entschuldigung! Die 8select DB Trigger konnten nicht entfernt werden. Bitte wenden Sie sich an service@8select.de und fügen Sie das aktuelle Plugin Log an. Vielen Dank.');
        }
    }

    private function removeConfig()
    {
        $this->connection->exec('DROP TABLE IF EXISTS `8s_plugin_cse_config`');
    }

    private function removeFeedLog()
    {
        $this->connection->exec('DROP TABLE IF EXISTS `8s_feeds`;');
    }

    private function createStatusExportDeltaTable()
    {
        $statusExportDelta = new StatusExportDelta($this->connection);
        $statusExportDelta->install();
    }
}
