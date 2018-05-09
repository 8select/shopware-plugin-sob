<?php


class Shopware_Controllers_Backend_EightSelectManualExport extends \Shopware_Controllers_Backend_ExtJs
{
    public function quickExportAction()
    {
        Shopware()->Container()->get('eight_select.quick_update')->doCron();
        $this->View()->assign(['success' => true]);
    }

    public function fullExportAction()
    {
        Shopware()->Container()->get('eight_select.article_export')->doCron();
        $this->View()->assign(['success' => true]);
    }
}
