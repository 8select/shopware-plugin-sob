<?php

use CseEightselectBasic\Components\ArticleExport;
use CseEightselectBasic\Components\FeedLogger;
use CseEightselectBasic\Components\ForceFullPropertyExport;
use CseEightselectBasic\Components\PropertyExport;
use CseEightselectBasic\Components\RunCronOnce;

class Shopware_Controllers_Backend_CseEightselectBasicManualExport extends \Shopware_Controllers_Backend_ExtJs
{
    public function fullExportAction()
    {
        RunCronOnce::runOnce(ArticleExport::CRON_NAME);
        RunCronOnce::runOnce(ForceFullPropertyExport::CRON_NAME);
    }

    public function propertyExportAction()
    {
        RunCronOnce::runOnce(PropertyExport::CRON_NAME);
    }

    public function getFullExportStatusAction()
    {
        $progressArticle = RunCronOnce::getProgress(ArticleExport::CRON_NAME);
        $progressProperty = RunCronOnce::getProgress(ForceFullPropertyExport::CRON_NAME);
        $progress = $progressArticle;
        if (is_numeric($progressArticle)) {
            $progress = $progressArticle / 2;
        }
        if (is_numeric($progressProperty)) {
            $progress = 50 + $progressProperty / 2;
        }

        $isRunning = RunCronOnce::isRunning(ArticleExport::CRON_NAME) || RunCronOnce::isRunning(ForceFullPropertyExport::CRON_NAME);
        $this->View()->assign(['progress' => $progress, 'isRunning' => $isRunning]);
    }

    public function getPropertyExportStatusAction()
    {
        $progress = RunCronOnce::getProgress(PropertyExport::CRON_NAME);
        $isRunning = RunCronOnce::isRunning(PropertyExport::CRON_NAME);
        $this->View()->assign(['progress' => $progress, 'isRunning' => $isRunning]);
    }

    public function getLastFullExportDateAction()
    {
        $lastRun = FeedLogger::getLastFeedUpdate(ArticleExport::CRON_NAME);
        $this->View()->assign(['lastFullExport' => $lastRun]);
    }

    public function getLastPropertyExportDateAction()
    {
        $lastRun = FeedLogger::getLastFeedUpdate(PropertyExport::CRON_NAME);
        $this->View()->assign(['lastPropertyExport' => $lastRun]);
    }
}
