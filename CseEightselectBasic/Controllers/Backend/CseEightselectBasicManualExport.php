<?php

class Shopware_Controllers_Backend_CseEightselectBasicManualExport extends \Shopware_Controllers_Backend_ExtJs
{
    public function quickExportAction()
    {
        Shopware()->Container()->get('cse_eightselect_basic.quick_update')->doCron();
        $this->View()->assign(['success' => true]);
    }

    public function fullExportAction()
    {
        Shopware()->Container()->get('cse_eightselect_basic.article_export')->doCron();
        $this->View()->assign(['success' => true]);
    }
}
