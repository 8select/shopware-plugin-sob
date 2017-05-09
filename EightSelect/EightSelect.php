<?php

namespace EightSelect;

use Shopware\Components\Emotion\ComponentInstaller;
use Shopware\Components\Plugin;
use Doctrine\Common\Collections\ArrayCollection;
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
            'Shopware_Controllers_Widgets_Emotion_AddElement' => 'onEmotionAddElement',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_EightSelect' => 'onGetEightSelectController',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Emotion' => 'onPostDispatchBackendEmotion',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onFrontendPostDispatch',
            'Enlight_Controller_Action_PostDispatch_Frontend_Checkout' => 'onCheckoutConfirm'
        ];
    }

    /**
     * @return string
     */
    public function onGetEightSelectController()
    {
        return $this->getPath() . '/Controllers/Frontend/EightSelect.php';
    }

    public function onEmotionAddElement(\Enlight_Event_EventArgs $args)
    {
        $data = $args->getReturn();
        $args->setReturn($data);
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return Shopware()->Db()->query('SELECT version FROM s_core_plugins WHERE name = ?', [$this->getName()])->fetchColumn();
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
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
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatchBackendEmotion(\Enlight_Controller_ActionEventArgs $args) {
        $controller = $args->getSubject();
        $view = $controller->View();

        $view->addTemplateDir($this->getPath() . '/Resources/views/');
        $view->extendsTemplate('backend/emotion/model/translations.js');
        $view->extendsTemplate('backend/emotion/eight_select/view/detail/elements/sys_psv.js');
        $view->extendsTemplate('backend/emotion/eight_select/view/detail/elements/psp_psv.js');
        $view->extendsTemplate('backend/emotion/eight_select/view/detail/elements/psp_tlv.js');
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
        $view->assign('checkoutFinish', true);
    }

    /**
     * @param InstallContext $context
     */
    public function install(InstallContext $context)
    {
        $this->installWidgets();
        parent::install($context);
    }

    public function installWidgets() {
        /** @var ComponentInstaller $installer */
        $installer = $this->container->get('shopware.emotion_component_installer');

        // component SYS-PSV
        $syspsvElement = $installer->createOrUpdate(
            $this->getName(),
            '8select SYS-PSV component',
            [
                'name' => 'SYS-PSV Component',
                'template' => 'sys_psv',
                'cls' => '8select--element--sys-psv',
                'xtype' => 'emotion-8select-syspsv-element',
            ]
        );
        $syspsvElement->createTextField(
            [
                'name' => 'sys_psv_ordernumber',
                'fieldLabel' => 'Product Ordernumber',
                'supportText' => 'Enter a product ordernumber.',
                'allowBlank' => false
            ]
        );

        // component PSP-TLV
        $psptlvElement = $installer->createOrUpdate(
            $this->getName(),
            '8select PSP-TLV component',
            [
                'name' => 'PSP-TLV Component',
                'template' => 'psp_tlv',
                'cls' => '8select--element--psp-tlv',
                'xtype' => 'emotion-8select-psptlv-element',
            ]
        );
        $psptlvElement->createTextField(
            [
                'name' => 'psp_tlv_stylefactor',
                'fieldLabel' => 'Style Factor',
                'supportText' => 'Enter a style factor.',
                'allowBlank' => false
            ]
        );

        // component PSP-PSV
        $psppsvElement = $installer->createOrUpdate(
            $this->getName(),
            '8select PSP-PSV component',
            [
                'name' => 'PSP-PSV Component',
                'template' => 'psp_psv',
                'cls' => '8select--element--psp-psv',
                'xtype' => 'emotion-8select-psppsv-element',
            ]
        );
        $psppsvElement->createTextField(
            [
                'name' => 'psp_psv_set_id',
                'fieldLabel' => 'Set-ID',
                'supportText' => 'Enter a set-id.',
                'allowBlank' => false
            ]
        );

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
