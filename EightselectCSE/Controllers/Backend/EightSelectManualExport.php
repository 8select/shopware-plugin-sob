<?php

class Shopware_Controllers_Backend_EightSelectManualExport extends \Shopware_Controllers_Backend_ExtJs
{
    public function quickExportAction()
    {
        Shopware()->Container()->get('eightselect_c_s_e.quick_update')->doCron();
        $this->View()->assign(['success' => true]);
    }

    public function fullExportAction()
    {
        Shopware()->Container()->get('eightselect_c_s_e.article_export')->doCron();
        $this->View()->assign(['success' => true]);
    }
}
