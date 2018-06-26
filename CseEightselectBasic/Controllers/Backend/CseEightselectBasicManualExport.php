<?php

use CseEightselectBasic\Components\ArticleExport;
use CseEightselectBasic\Components\QuickUpdate;
use CseEightselectBasic\Components\RunCronOnce;
use CseEightselectBasic\Components\FeedLogger;

class Shopware_Controllers_Backend_CseEightselectBasicManualExport extends \Shopware_Controllers_Backend_ExtJs
{
    public function fullExportAction()
    {
        RunCronOnce::runOnce(ArticleExport::CRON_NAME);
    }

    public function quickExportAction()
    {
        RunCronOnce::runOnce(QuickUpdate::CRON_NAME);
    }

    public function getFullExportStatusAction()
    {
        $progress = RunCronOnce::getProgress(ArticleExport::CRON_NAME);
        $this->View()->assign(['progress' => $progress]);
    }

    public function getQuickExportStatusAction()
    {
        $progress = RunCronOnce::getProgress(QuickUpdate::CRON_NAME);
        $this->View()->assign(['progress' => $progress]);
    }

    public function getLastFullExportDateAction() {
        $lastRun = FeedLogger::getLastFeedUpdate(ArticleExport::CRON_NAME);
        $this->View()->assign(['lastFullExport' => $lastRun]);
    }

    public function getLastQuickUpdateDateAction() {
        $lastRun = FeedLogger::getLastFeedUpdate(QuickUpdate::CRON_NAME);
        $this->View()->assign(['lastQuickUpdate' => $lastRun]);
    }
}
