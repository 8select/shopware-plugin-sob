<?php

namespace EightSelect;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;

class EightSelect extends Plugin
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Theme_Compiler_Collect_Plugin_Javascript' => 'addJsFiles',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onFrontendPostDispatch',
            'Enlight_Controller_Action_PostDispatch_Frontend_Checkout' => 'onCheckoutConfirm'
        ];
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return Shopware()->Db()->query('SELECT version FROM s_core_plugins WHERE name = ?', [$this->getName()])->fetchColumn();
    }

    public function onFrontendPostDispatch(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $view = $controller->View();
        $view->addTemplateDir($this->getPath() . '/Resources/views/');

        $this->config = Shopware()->Config();
        $htmlContainer = $this->config->get('html_container_element');
        $view->assign('htmlContainer', explode('CSE_SYS', $htmlContainer));
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onCheckoutConfirm(\Enlight_Event_EventArgs $args) {
        $request = $args->getRequest();
        $controller = $args->get('subject');
        $view = $controller->View();
        if ($request->getActionName() != "finish")
        {
            return;
        }
        // todo JSON for 8select analytics
        $view->assign('checkoutFinish', true);
    }

    /**
     * @param InstallContext $context
     */
    public function install(InstallContext $context)
    {
        parent::install($context);
    }

    /**
     * @param UninstallContext $context
     */
    public function uninstall(UninstallContext $context)
    {
        parent::uninstall($context);
    }

    /**
     * Provide the file collection for js files
     *
     * @return ArrayCollection
     */
    public function addJsFiles()
    {
        $jsDir = __DIR__ . '/Resources/views/frontend/_public/src/js/';
        $jsFiles = [
            $jsDir . 'jquery.8select-csePlugin.js',
        ];

        return new ArrayCollection($jsFiles);
    }

}
