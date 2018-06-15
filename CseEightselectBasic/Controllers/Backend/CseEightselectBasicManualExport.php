<?php

use CseEightselectBasic\Components\ArticleExport;
use CseEightselectBasic\Components\QuickUpdate;
use CseEightselectBasic\Components\RunCronOnce;
use CseEightselectBasic\Components\FeedLogger;
use CseEightselectBasic\Components\ConfigValidator;

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

    public function getLastFullExportDateAction() 
    {
        $lastRun = FeedLogger::getLastFeedUpdate(ArticleExport::CRON_NAME);
        $this->View()->assign(['lastFullExport' => $lastRun]);
    }

    public function getLastQuickUpdateDateAction() 
    {
        $lastRun = FeedLogger::getLastFeedUpdate(QuickUpdate::CRON_NAME);
        $this->View()->assign(['lastQuickUpdate' => $lastRun]);
    }

    public function checkForActiveStateAction()
    {
        $isActive = ConfigValidator::isPluginActive();
        $this->View()->assign(['active' => $isActive]);
    }
    
    public function checkForApiIdAction()
    {
        $apiId = ConfigValidator::getApiId();
        $this->View()->assign(['apiId' => $apiId]);
    }

    public function checkForFeedIdAction()
    {
        $feedId = ConfigValidator::getFeedId();
        $this->View()->assign(['feedId' => $feedId]);
    }

    public function checkForHtmlContainerAction()
    {
        $container = ConfigValidator::getHtmlContainer();
        $this->View()->assign(['container' => $container]);
    }

    public function checkForSysAccAction()
    {
        $sysAcc = ConfigValidator::isSysAccActive();
        $this->View()->assign(['sysAcc' => $sysAcc]);
    }

    public function checkForPreviewModeAction()
    {
        $previewMode = ConfigValidator::isPreviewModeActive();
        $this->View()->assign(['previewMode' => $previewMode]);
    }

    public function checkForSizeDefinitions()
    {
        $sizeDefinitions = ConfigValidator::hasSizeDefinitions();
        $this->View()->assign(['sizeDefinitions' => $sizeDefinitions]);
    }
}
