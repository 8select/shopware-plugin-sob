<?php

use CseEightselectBasic\Components\ArticleExport;
use CseEightselectBasic\Components\QuickUpdate;
use CseEightselectBasic\Components\RunCronOnce;
use CseEightselectBasic\Components\ConfigValidator;

class Shopware_Controllers_Backend_CseEightselectBasicConfigValidation extends \Shopware_Controllers_Backend_ExtJs
{
    public function validateAction()
    {
        $validationResult = [
            'isValid' => false,
            'messages' => [
                'foo',
                'bar'
            ]
            ];

        $this->View()->assign(['validationResult' => $validationResult]);
    }


    // public function checkForApiIdAction()
    // {
    //     $apiId = ConfigValidator::getApiId();
    //     $this->View()->assign(['apiId' => $apiId]);
    // }

    // public function checkForFeedIdAction()
    // {
    //     $feedId = ConfigValidator::getFeedId();
    //     $this->View()->assign(['feedId' => $feedId]);
    // }

    // public function checkForHtmlContainerAction()
    // {
    //     $container = ConfigValidator::getHtmlContainer();
    //     $this->View()->assign(['container' => $container]);
    // }

    // public function checkForSysAccAction()
    // {
    //     $sysAcc = ConfigValidator::isSysAccActive();
    //     $this->View()->assign(['sysAcc' => $sysAcc]);
    // }

    // public function checkForPreviewModeAction()
    // {
    //     $previewMode = ConfigValidator::isPreviewModeActive();
    //     $this->View()->assign(['previewMode' => $previewMode]);
    // }

    // public function checkForSizeDefinitionsAction()
    // {
    //     $sizeDefinitions = ConfigValidator::hasSizeDefinitions();
    //     $this->View()->assign(['sizeDefinitions' => $sizeDefinitions]);
    // }
}
