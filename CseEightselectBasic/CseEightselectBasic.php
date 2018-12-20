<?php
namespace CseEightselectBasic;

use CseEightselectBasic\Components\AWSUploader;
use CseEightselectBasic\Components\ExportSetup;
use CseEightselectBasic\Components\FeedLogger;
use CseEightselectBasic\Components\FieldHelper;
use CseEightselectBasic\Components\RunCronOnce;
use CseEightselectBasic\Models\EightselectAttribute;
use CseEightselectBasic\Services\Config\Config;
use CseEightselectBasic\Services\PluginConfig\PluginConfig as PluginConfigService;
use CseEightselectBasic\Setup\Database\Migrations\Update_1_11_0;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\SchemaTool;
use Shopware;
use Shopware\Components\Emotion\ComponentInstaller;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Models\Shop\Shop;

class CseEightselectBasic extends Plugin
{
    /**
     * array
     */
    private $installMessages = [];

    /**
     * PluginConfigService
     */
    private $pluginConfigService;

    private function initInstallLog($context)
    {
        try {
            $this->installMessages[] = 'Shop-URL: ' . FieldHelper::getFallbackBaseUrl();
            $this->installMessages[] = 'Shopware-Version: ' . $this->container->get('shopware.release')->getVersion();
            $this->installMessages[] = 'CSE-Plugin-Version: ' . $context->getCurrentVersion();
        } catch (\Exception $exception) {
            $this->installMessages[] = 'ERROR: initInstallLog ' . (string) $exception;
        }
    }

    private function sendLog($type = 'install')
    {
        $logMessage = implode(\PHP_EOL . \PHP_EOL, $this->installMessages);
        AWSUploader::uploadLog($logMessage, $type);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch' => 'onPreDispatch',
            'Theme_Compiler_Collect_Plugin_Javascript' => 'addJsFiles',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_CseEightselectBasic' => 'onGetFrontendCseEightselectBasicController',
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_CseEightselectBasic' => 'onGetBackendCseEightselectBasicController',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Emotion' => 'onPostDispatchBackendEmotion',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onFrontendPostDispatch',
            'Enlight_Controller_Action_PostDispatch_Frontend_Checkout' => 'onCheckoutConfirm',
            'Shopware_CronJob_CseEightselectBasicArticleExport' => 'cseEightselectBasicArticleExport',
            'Shopware_CronJob_CseEightselectBasicArticleExportOnce' => 'cseEightselectBasicArticleExportOnce',
            'Shopware_CronJob_CseEightselectBasicPropertyExport' => 'cseEightselectBasicPropertyExport',
            'Shopware_CronJob_CseEightselectBasicPropertyExportOnce' => 'cseEightselectBasicPropertyExportOnce',
            'Shopware_Controllers_Backend_Config_After_Save_Config_Element' => 'onBackendConfigSave',
        ];
    }

    private function getPluginConfigService()
    {
        if (!isset($this->pluginConfigService)) {
            $configReader = $this->container->get('shopware.plugin.cached_config_reader');
            $this->pluginConfigService = new PluginConfigService($this->container, $configReader, $this->getName());
        }

        return $this->pluginConfigService;
    }

    private function dumper()
    {
        // $provider = $this->container->get('cse_eightselect_basic.dependencies.provider');
        // $shop = $provider->getCurrentShop();
        // dump([
        //     'message' => 'config check - onFrontendPostDispatch',
        //     'shop-name' => $shop->getName(),
        //     'shop-category' => $shop->getCategory()->getId(),
        //     'shop-host' => $shop->getHost(),
        //     'current-shop' => $shop,
        //     'active-shop' => $provider->getShopWithActiveCSE(),
        //     'cse-active-for-currentshop' => $this->getPluginConfigService()->isCseActiveForCurrentShop(),
        //     'cse-config' =>  $this->getPluginConfigService()->debug(),
        //     'shopware-config-sBASEFILE' =>  $this->container->get('config')['sBASEFILE'],
        //     'link' => $link
        // ]);
    }

    public function onPreDispatch()
    {
        Shopware()->Template()->addTemplateDir($this->getPath() . '/Resources/views/');
    }

    /**
     * @return string
     */
    public function onGetBackendCseEightselectBasicController()
    {
        return $this->getPath() . '/Controllers/Backend/CseEightselectBasicAttributeConfig.php';
    }

    /**
     * @return string
     */
    public function onGetFrontendCseEightselectBasicController()
    {
        return $this->getPath() . '/Controllers/Frontend/CseEightselectBasic.php';
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return string
     */
    public function getVersion()
    {
        // brauchen wir das hier? wo wird das benutzt?
        return Shopware()->Db()->query(
            'SELECT version FROM s_core_plugins WHERE name = ?',
            [$this->getName()]
        )->fetchColumn();
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onFrontendPostDispatch(\Enlight_Event_EventArgs $args)
    {
        $this->dumper();
        if ($this->getPluginConfigService()->isCseActiveForCurrentShop() === false) {
            dump('CSE is disabled -> stop');
            return;
        }

        dump('CSE is enabled -> proceed');

        $args->get('subject')->View()->assign(
            'htmlContainer',
            explode('CSE_SYS', $this->getPluginConfigService()->get('CseEightselectBasicSysPsvContainer'))
        );
        $args->get('subject')->View()->assign(
            'htmlSysAccContainer',
            explode('CSE_SYS', $this->getPluginConfigService()->get('CseEightselectBasicSysAccContainer'))
        );
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatchBackendEmotion(\Enlight_Controller_ActionEventArgs $args)
    {
        $this->dumper();
        // sollte ggf. immer aktiv sein damit emotion benutzt werden kann?
        if ($this->getPluginConfigService()->isCseActive() === false) {
            dump('CSE is disabled -> stop');
            return;
        }

        dump('CSE is enabled -> proceed');

        $controller = $args->getSubject();
        $view = $controller->View();

        $view->addTemplateDir($this->getPath() . '/Resources/views/');
        $view->extendsTemplate('backend/emotion/model/translations.js');
        $view->extendsTemplate('backend/emotion/cse_eightselect_basic/view/detail/elements/sys_psv.js');
        $view->extendsTemplate('backend/emotion/cse_eightselect_basic/view/detail/elements/psp_psv.js');
        $view->extendsTemplate('backend/emotion/cse_eightselect_basic/view/detail/elements/psp_tlv.js');
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @throws \Enlight_Exception
     */
    public function onCheckoutConfirm(\Enlight_Event_EventArgs $args)
    {
        $this->dumper();

        if ($this->getPluginConfigService()->isCseActiveForCurrentShop() === false) {
            dump('CSE is disabled -> stop');
            return;
        }

        dump('CSE is enabled -> proceed');

        $request = $args->getRequest();
        $controller = $args->get('subject');
        $view = $controller->View();
        if ($request->getActionName() != 'finish') {
            return;
        }

        /** @var \Shopware\Models\Shop\Currency $currentCurrency */
        $currentCurrency = Shopware()->Shop()->getCurrency();

        if ($currentCurrency->getCurrency() == 'EUR' && $currentCurrency->getDefault()) {
            $factor = $currentCurrency->getFactor();
        } else {
            $currencies = Shopware()->Shop()->getCurrencies();
            /** @var \Shopware\Models\Shop\Currency $loopCurrency */
            foreach ($currencies as $loopCurrency) {
                if ($loopCurrency->getCurrency() == 'EUR') {
                    $factor = $loopCurrency->getFactor();
                }
            }
        }

        $sBasket = $view->sBasket;
        foreach ($sBasket as &$basketItem) {
            foreach ($basketItem as &$itemProperty) {
                if ($itemProperty['price'] != null) {
                    $itemProperty = $this->calculatePrice($itemProperty, $factor);
                }
            }
        }
        $view->assign('sBasket', $sBasket);
        $view->assign('checkoutFinish', true);
    }

    /**
     * @param $itemProperty
     * @param $factor
     * @return mixed
     */
    protected function calculatePrice($itemProperty, $factor)
    {
        $tempPrice = (strpos($itemProperty['price'], ',') != false) ? str_replace(
            ',',
            '.',
            $itemProperty['price']
        ) : $itemProperty['price'];
        if ($itemProperty['currencyFactor'] > 0) {
            $tempPrice = $tempPrice / $itemProperty['currencyFactor'];
        }
        $itemProperty['intprice'] = round($tempPrice * 100 * $factor);

        return $itemProperty;
    }

    /**
     * @param InstallContext $context
     * @throws \Exception
     */
    public function install(InstallContext $context)
    {
        $this->initInstallLog($context);

        $this->removeExportCron();
        $this->addExportCron();
        $this->removeExportOnceCron();
        $this->addExportOnceCron();
        $this->removePropertyCron();
        $this->addPropertyCron();
        $this->removePropertyOnceCron();
        $this->addPropertyOnceCron();
        $this->installWidgets();
        $this->createDatabase($context);
        $this->createAttributes();

        $this->sendLog('install');

        return parent::install($context);
    }

    /**
     * @param ActivateContext $context
     */
    public function activate(ActivateContext $context)
    {
        $this->initInstallLog($context);
        $this->sendLog('activate');
        return $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }

    /**
     * @param DeactivateContext $context
     */
    public function deactivate(DeactivateContext $context)
    {
        $this->initInstallLog($context);
        $this->sendLog('deactivate');
        return $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }

    /**
     * @param UpdateContext $context
     */
    public function update(UpdateContext $context)
    {
        $this->initInstallLog($context);

        switch (true) {
            case version_compare($context->getCurrentVersion(), '1.0.1', '<='):
                $this->update_1_0_1();
            case version_compare($context->getCurrentVersion(), '1.5.0', '<='):
                $this->update_1_5_0();
            case version_compare($context->getCurrentVersion(), '1.5.2', '<='):
                $this->update_1_5_2();
            case version_compare($context->getCurrentVersion(), '1.6.3', '<='):
                $this->update_1_6_3();
            case version_compare($context->getCurrentVersion(), '1.6.4', '<='):
                $this->update_1_6_4();
            case version_compare($context->getCurrentVersion(), '1.8.0', '<='):
                $this->update_1_8_0();
            case version_compare($context->getCurrentVersion(), '1.11.0', '<='):
                $update = new Update_1_11_0($this->container->get('config'), $this->container->get('config_writer'));
                $update->update();
        }

        $this->installMessages[] = 'Update auf CSE-Plugin-Version: ' . $context->getUpdateVersion();
        $this->sendLog('update');

        return $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }

    private function update_1_0_1()
    {
        $this->deleteExportDir();
    }

    private function update_1_5_0()
    {
        FeedLogger::createTable();
    }

    private function update_1_5_2()
    {
        // remove quick update
        $this->removeQuickUpdateCron();
        $this->removeQuickUpdateOnceCron();
        FeedLogger::deleteFeedEntryByName('8select_update_export');
        // add property update
        $this->addPropertyCron();
        $this->addPropertyOnceCron();
        // update changeQueue triggers
        ExportSetup::dropChangeQueueTriggers();
        ExportSetup::createChangeQueueTriggers();
    }

    private function update_1_6_3()
    {
        // update changeQueue table and triggers
        ExportSetup::dropChangeQueueTriggers();
        ExportSetup::dropChangeQueueTable();
        ExportSetup::createChangeQueueTable();
        ExportSetup::createChangeQueueTriggers();
    }

    private function update_1_6_4()
    {
        $this->deleteExportDir();
    }

    private function update_1_8_0()
    {
        $this->createDefaultConfig();
    }

    /**
     * Create attributes
     *
     * @throws \Exception
     */
    private function createAttributes()
    {
        /** @var \Shopware\Bundle\AttributeBundle\Service\CrudService $attributeService */
        $attributeService = Shopware()->Container()->get('shopware_attribute.crud_service');

        $attributeService->update('s_article_configurator_groups_attributes', 'od_cse_eightselect_basic_is_size', 'boolean', [
            'label' => 'Definiert Größe',
            'displayInBackend' => true,
            'custom' => false,
            'translatable' => false,
            'position' => 0,
        ]);

        /** @var \Doctrine\Common\Cache\ClearableCache $metaDataCache */
        $metaDataCache = Shopware()->Models()->getConfiguration()->getMetadataCacheImpl();
        $metaDataCache->deleteAll();
        Shopware()->Models()->generateAttributeModels(['s_filter_options_attributes']);
    }

    /**
     * @throws \Exception
     */
    public function installWidgets()
    {
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
        $syspsvElement->createHiddenField(
            [
                'name' => 'sys_psv_ordernumber',
                'fieldLabel' => 'Product Ordernumber',
                'allowBlank' => false,
            ]
        );
        $syspsvElement->createNumberField(
            [
                'name' => 'sys_psv_lazyload_factor',
                'fieldLabel' => 'Lazy Load Distance Factor',
                'defaultValue' => 0,
                'helpText' => 'Definiert einen Faktor auf Basis der Fensterhöhe, ab dem das Widget unterhalb des
                                sichtbaren Scrollbereiches vorgeladen werden soll ("lazy loading"). Beispiel: 0 = Laden,
                                sobald sich das Widget direkt unterhalb des sichtbaren Bereiches befindet; 1 = Laden,
                                sobald sich das Widget eine Fensterhöhe weit unterhalb des sichtbaren Bereiches
                                befindet.',
                'allowBlank' => true,
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
                'fieldLabel' => 'Stylefactor',
                'allowBlank' => false,
            ]
        );
        $psptlvElement->createNumberField(
            [
                'name' => 'psp_tlv_lazyload_factor',
                'fieldLabel' => 'Lazy Load Distance Factor',
                'defaultValue' => 0,
                'helpText' => 'Definiert einen Faktor auf Basis der Fensterhöhe, ab dem das Widget unterhalb des
                                sichtbaren Scrollbereiches vorgeladen werden soll ("lazy loading"). Beispiel: 0 = Laden,
                                sobald sich das Widget direkt unterhalb des sichtbaren Bereiches befindet; 1 = Laden,
                                sobald sich das Widget eine Fensterhöhe weit unterhalb des sichtbaren Bereiches
                                befindet.',
                'allowBlank' => true,
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
                'allowBlank' => false,
            ]
        );
        $psppsvElement->createNumberField(
            [
                'name' => 'psp_psv_lazyload_factor',
                'fieldLabel' => 'Lazy Load Distance Factor',
                'defaultValue' => 0,
                'helpText' => 'Definiert einen Faktor auf Basis der Fensterhöhe, ab dem das Widget unterhalb des
                                sichtbaren Scrollbereiches vorgeladen werden soll ("lazy loading"). Beispiel: 0 = Laden,
                                sobald sich das Widget direkt unterhalb des sichtbaren Bereiches befindet; 1 = Laden,
                                sobald sich das Widget eine Fensterhöhe weit unterhalb des sichtbaren Bereiches
                                befindet.',
                'allowBlank' => true,
            ]
        );
    }

    /**
     * @param UninstallContext $context
     * @throws \Exception
     */
    public function uninstall(UninstallContext $context)
    {
        $this->initInstallLog($context);

        $this->removeExportCron();
        $this->removeExportOnceCron();
        $this->removePropertyCron();
        $this->removePropertyOnceCron();
        $this->removeDatabase();
        $this->deleteExportDir();
        $this->removeAttributes();

        $this->sendLog('uninstall');

        return $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }

    private function updateSchema()
    {
        $modelManager = $this->container->get('models');
        $tool = new SchemaTool($modelManager);
        $classes = $this->getClasses($modelManager);
        $tool->updateSchema($classes, true);
    }

    private function dropSchema()
    {
        $modelManager = $this->container->get('models');
        $tool = new SchemaTool($modelManager);
        $classes = $this->getClasses($modelManager);
        $tool->dropSchema($classes);
    }

    private function createDefaultConfig()
    {
        $config = new Config($this->container->get('dbal_connection'));
        $config->install();
        $config->setOption(Config::OPTION_EXPORT_TYPE, Config::OPTION_EXPORT_TYPE_VALUE_DELTA);
    }

    /**
     * @param InstallContext $context
     * @throws \Zend_Db_Adapter_Exception
     */
    private function createDatabase(InstallContext $context)
    {
        $this->createDefaultConfig();
        $this->updateSchema();
        $this->initAttributes();
        ExportSetup::createChangeQueueTable();
        try {
            ExportSetup::createChangeQueueTriggers();
        } catch (\Zend_Db_Statement_Exception $exception) {
            $config = new Config($this->container->get('dbal_connection'));
            $config->setOption(Config::OPTION_EXPORT_TYPE, Config::OPTION_EXPORT_TYPE_VALUE_FULL);
            ExportSetup::dropChangeQueueTable();

            $message = 'DB Trigger für Delta-Export nicht installiert. Fallback zu Vollexport.';
            $context->scheduleMessage($message);
            $this->installMessages[] = $message;
            $this->installMessages[] = (string) $exception;
        }
        RunCronOnce::createTable();
        FeedLogger::createTable();
    }

    private function removeDatabase()
    {
        $this->dropSchema();
        RunCronOnce::deleteTable();
        FeedLogger::deleteTable();
        ExportSetup::dropChangeQueueTriggers();
        ExportSetup::dropChangeQueueTable();
        $config = new Config($this->container->get('dbal_connection'));
        $config->uninstall();
    }

    /**
     * @throws \Exception
     */
    private function removeAttributes()
    {
        /** @var \Shopware\Bundle\AttributeBundle\Service\CrudService $attributeService */
        $attributeService = Shopware()->Container()->get('shopware_attribute.crud_service');

        $attributeService->delete('s_article_configurator_groups_attributes', 'od_cse_eightselect_basic_is_size');
    }

    /**
     * @param ModelManager $modelManager
     * @return array
     */
    private function getClasses(ModelManager $modelManager)
    {
        return [
            $modelManager->getClassMetadata(EightselectAttribute::class),
        ];
    }

    /**
     * @param \Shopware_Components_Cron_CronJob $job
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public function cseEightselectBasicArticleExport(\Shopware_Components_Cron_CronJob $job)
    {
        $this->dumper();
        if ($this->getPluginConfigService()->isCseActive() === false) {
            dump('CSE is disabled -> stop');
            return;
        }

        dump('CSE is enabled -> proceed');

        try {
            $this->container->get('cse_eightselect_basic.article_export')->scheduleCron();
            $this->container->get('cse_eightselect_basic.article_export')->doCron();
            $this->container->get('cse_eightselect_basic.force_full_property_export')->scheduleCron();
            $this->container->get('cse_eightselect_basic.force_full_property_export')->doCron();
        } catch (\Exception $exception) {
            return sprintf('Export fehlgeschlagen: %s', $exception->getMessage());
        }

        return 'Export erfolgreich';
    }

    /**
     * @param \Shopware_Components_Cron_CronJob $job
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public function cseEightselectBasicArticleExportOnce(\Shopware_Components_Cron_CronJob $job)
    {
        $this->dumper();

        if ($this->getPluginConfigService()->isCseActive() === false) {
            dump('CSE is disabled -> stop');
            return;
        }

        dump('CSE is enabled -> proceed');

        try {
            $this->container->get('cse_eightselect_basic.article_export')->doCron();
            $this->container->get('cse_eightselect_basic.force_full_property_export')->doCron();
        } catch (\Exception $exception) {
            return sprintf('Export fehlgeschlagen: %s', $exception->getMessage());
        }

        return 'Export erfolgreich';
    }

    /**
     * @param \Shopware_Components_Cron_CronJob $job
     * @throws \Exception
     */
    public function cseEightselectBasicPropertyExport(\Shopware_Components_Cron_CronJob $job)
    {
        $this->dumper();

        if ($this->getPluginConfigService()->isCseActive() === false) {
            dump('CSE is disabled -> stop');
            return;
        }

        dump('CSE is enabled -> proceed');

        try {
            $this->container->get('cse_eightselect_basic.property_export')->scheduleCron();
            $this->container->get('cse_eightselect_basic.property_export')->doCron();
        } catch (\Exception $exception) {
            return sprintf('Export fehlgeschlagen: %s', $exception->getMessage());
        }

        return 'Export erfolgreich';
    }

    /**
     * @param  \Shopware_Components_Cron_CronJob $job
     * @throws \Exception
     */
    public function cseEightselectBasicPropertyExportOnce(\Shopware_Components_Cron_CronJob $job)
    {
        $this->dumper();

        if ($this->getPluginConfigService()->isCseActive() === false) {
            dump('CSE is disabled -> stop');
            return;
        }

        dump('CSE is enabled -> proceed');

        try {
            $this->container->get('cse_eightselect_basic.property_export')->doCron();
        } catch (\Exception $exception) {
            return sprintf('Export fehlgeschlagen: %s', $exception->getMessage());
        }

        return 'Export erfolgreich';
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

    /**
     * @throws \Exception
     */
    public function addExportCron()
    {
        $connection = $this->container->get('dbal_connection');
        $connection->insert(
            's_crontab',
            [
                'name' => '8select article export',
                'action' => 'Shopware_CronJob_CseEightselectBasicArticleExport',
                'next' => $this->getNextMidnight(),
                'start' => null,
                '`interval`' => '86400',
                'active' => 1,
                'end' => new \DateTime(),
                'pluginID' => $this->container->get('shopware.plugin_manager')->getPluginByName($this->getName())->getId(),
            ],
            [
                'next' => 'datetime',
                'end' => 'datetime',
            ]
        );
    }

    public function removeExportCron()
    {
        $this->container->get('dbal_connection')->executeQuery('DELETE FROM s_crontab WHERE `action` = ?', [
            'Shopware_CronJob_CseEightselectBasicArticleExport',
        ]);
    }

    /**
     * @throws \Exception
     */
    public function addExportOnceCron()
    {
        $connection = $this->container->get('dbal_connection');
        $connection->insert(
            's_crontab',
            [
                'name' => '8select article export once',
                'action' => 'Shopware_CronJob_CseEightselectBasicArticleExportOnce',
                'next' => new \DateTime(),
                'start' => null,
                '`interval`' => '1',
                'active' => 1,
                'end' => new \DateTime(),
                'pluginID' => $this->container->get('shopware.plugin_manager')->getPluginByName($this->getName())->getId(),
            ],
            [
                'next' => 'datetime',
                'end' => 'datetime',
            ]
        );
    }

    public function removeExportOnceCron()
    {
        $this->container->get('dbal_connection')->executeQuery('DELETE FROM s_crontab WHERE `action` = ?', [
            'Shopware_CronJob_CseEightselectBasicArticleExportOnce',
        ]);
    }

    /**
     * add cron job for exporting all properties
     */
    public function addPropertyCron()
    {
        $connection = $this->container->get('dbal_connection');
        $connection->insert(
            's_crontab',
            [
                'name' => '8select property export',
                'action' => 'Shopware_CronJob_CseEightselectBasicPropertyExport',
                'next' => new \DateTime(),
                'start' => null,
                '`interval`' => '60',
                'active' => 1,
                'end' => new \DateTime(),
                'pluginID' => $this->container->get('shopware.plugin_manager')->getPluginByName($this->getName())->getId(),
            ],
            [
                'next' => 'datetime',
                'end' => 'datetime',
            ]
        );
    }

    public function removePropertyCron()
    {
        $this->container->get('dbal_connection')->executeQuery('DELETE FROM s_crontab WHERE `action` = ?', [
            'Shopware_CronJob_CseEightselectBasicPropertyExport',
        ]);
    }

    /**
     * add cron job for exporting all properties
     */
    public function addPropertyOnceCron()
    {
        $connection = $this->container->get('dbal_connection');
        $connection->insert(
            's_crontab',
            [
                'name' => '8select property export once',
                'action' => 'Shopware_CronJob_CseEightselectBasicPropertyExportOnce',
                'next' => new \DateTime(),
                'start' => null,
                '`interval`' => '1',
                'active' => 1,
                'end' => new \DateTime(),
                'pluginID' => $this->container->get('shopware.plugin_manager')->getPluginByName($this->getName())->getId(),
            ],
            [
                'next' => 'datetime',
                'end' => 'datetime',
            ]
        );
    }

    public function removePropertyOnceCron()
    {
        $this->container->get('dbal_connection')->executeQuery('DELETE FROM s_crontab WHERE `action` = ?', [
            'Shopware_CronJob_CseEightselectBasicPropertyExportOnce',
        ]);
    }

    /**
     * quick update remove methods for version <= 1.5.2
     */
    public function removeQuickUpdateCron()
    {
        $this->container->get('dbal_connection')->executeQuery('DELETE FROM s_crontab WHERE `action` = ?', [
            'Shopware_CronJob_CseEightselectBasicQuickUpdate',
        ]);
    }

    public function removeQuickUpdateOnceCron()
    {
        $this->container->get('dbal_connection')->executeQuery('DELETE FROM s_crontab WHERE `action` = ?', [
            'Shopware_CronJob_CseEightselectBasicQuickUpdateOnce',
        ]);
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     */
    private function initAttributes()
    {
        $attributeList = [
            [
                'eightselectAttribute' => 'ean',
                'eightselectAttributeLabel' => 'EAN-CODE',
                'eightselectAttributeLabelDescr' => 'Standardisierte eindeutige Materialnummer nach EAN (European Article Number) oder UPC (Unified Product Code).',
                'shopwareAttribute' => 's_articles_details.ean',
            ],
            [
                'eightselectAttribute' => 'name1',
                'eightselectAttributeLabel' => 'ARTIKELBEZEICHNUNG',
                'eightselectAttributeLabelDescr' => 'Standardbezeichnung für den Artikel so wie er normalerweise in der Artikeldetailansicht genutzt wird (z.B. Sportliches Herren-Hemd "Arie")',
                'shopwareAttribute' => 's_articles.name',
            ],
            [
                'eightselectAttribute' => 'name2',
                'eightselectAttributeLabel' => 'ALTERNATIVE ARTIKELBEZEICHNUNG',
                'eightselectAttributeLabelDescr' => 'Oft als Kurzbezeichnung in Listenansichten verwendet (z.B. "Freizeit-Hemd") oder für Google mit mehr Infos zur besseren Suche',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'beschreibung',
                'eightselectAttributeLabel' => 'BESCHREIBUNGSTEXT HTML',
                'eightselectAttributeLabelDescr' => 'Der Beschreibungstext zum Artikel, auch "description long" genannt, im HTML-Format z.B. "<p>Federleichte Regenhose! </ br> ...</p>"',
                'shopwareAttribute' => 's_articles.description_long',
            ],
            [
                'eightselectAttribute' => 'beschreibung2',
                'eightselectAttributeLabel' => 'ALTERNATIVER BESCHREIBUNGSTEXT',
                'eightselectAttributeLabelDescr' => 'zusätzliche Informationen zum Produkt, technische Beschreibung, Kurzbeschreibung oder auch Keywords',
                'shopwareAttribute' => 's_articles.keywords',
            ],
            [
                'eightselectAttribute' => 'rubrik',
                'eightselectAttributeLabel' => 'PRODUKTKATEGORIE / -RUBRIK',
                'eightselectAttributeLabelDescr' => 'bezeichnet spezielle Artikelgruppen, die als Filter oder Shop-Navigation genutzt werden (z.B. Große Größen, Umstandsmode, Stillmode)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'typ',
                'eightselectAttributeLabel' => 'PRODUKTTYP / UNTERKATEGORIE',
                'eightselectAttributeLabelDescr' => 'verfeinerte Shop-Navigation oder Unterkategorie (z.B. Lederjacke, Blouson, Parka)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'abteilung',
                'eightselectAttributeLabel' => 'ABTEILUNG',
                'eightselectAttributeLabelDescr' => 'Einteilung der Sortimente nach Zielgruppen  (z.B. Damen, Herren, Kinder)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'kiko',
                'eightselectAttributeLabel' => 'KIKO',
                'eightselectAttributeLabelDescr' => 'Speziell für Kindersortimente: Einteilung nach Zielgruppen (z.B. Mädchen, Jungen, Baby)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'bereich',
                'eightselectAttributeLabel' => 'BEREICH',
                'eightselectAttributeLabelDescr' => 'Damit können Teilsortimente bezeichnet sein (z.B. Outdoor; Kosmetik; Trachten; Lifestyle)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'sportart',
                'eightselectAttributeLabel' => 'SPORTART',
                'eightselectAttributeLabelDescr' => 'speziell bei Sportartikeln (z.B. Handball, Bike, Bergsteigen)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'serie',
                'eightselectAttributeLabel' => 'SERIE',
                'eightselectAttributeLabelDescr' => 'Hier können Bezeichnungen für Serien übergeben werden, um Artikelfamilien oder Sondereditionen zu kennzeichnen (z.B. Expert Line, Mountain Professional)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'gruppe',
                'eightselectAttributeLabel' => 'GRUPPE / BAUKAUSTEN',
                'eightselectAttributeLabelDescr' => 'bezeichnet direkt zusammengehörige Artikel (z.B. Bikini-Oberteil "Aloha" und Bikini-Unterteil "Aloha" = Gruppe 1002918; Baukasten-Sakko "Ernie" und Baukasten-Hose "Bert" = Gruppe "E&B"). Dabei können auch mehr als 2 Artikel eine Gruppe bilden (z.B. Mix & Match: Gruppe "Hawaii" = 3 Bikini-Oberteile können mit 2 Bikini-Unterteilen frei kombiniert werden) . Die ID für eine Gruppe kann eine Nummer oder ein Name sein.',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'saison',
                'eightselectAttributeLabel' => 'SAISON',
                'eightselectAttributeLabelDescr' => 'Beschreibt zu welcher Saison bzw. saisonalen Kollektion der Artikel gehört (z.B. HW18/19; Sommer 2018; Winter)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'farbe',
                'eightselectAttributeLabel' => 'FARBE',
                'eightselectAttributeLabelDescr' => 'Die exakte Farbbezeichnung des Artikels (z.B. Gelb; Himbeerrot; Rosenrot)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'farbspektrum',
                'eightselectAttributeLabel' => 'FARBSPEKTRUM',
                'eightselectAttributeLabelDescr' => 'Farben sind einem Farbspektrum zugeordnet (z.B. Farbe: Himbeerrot > Farbspektrum: Rot)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'muster',
                'eightselectAttributeLabel' => 'MUSTER',
                'eightselectAttributeLabelDescr' => 'Farbmuster des Artikels (z.B. uni, einfarbig,  kariert, gestreift, Blumenmuster, einfarbig-strukturiert)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'waschung',
                'eightselectAttributeLabel' => 'WASCHUNG',
                'eightselectAttributeLabelDescr' => 'optische Wirkung des Materials (bei Jeans z.B.  used, destroyed, bleached, vintage)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'stil',
                'eightselectAttributeLabel' => 'STIL',
                'eightselectAttributeLabelDescr' => 'Stilrichtung des Artikels (z.B.  Business, Casual,  Ethno, Retro)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'detail',
                'eightselectAttributeLabel' => 'DETAIL',
                'eightselectAttributeLabelDescr' => 'erwähnenswerte Details an Artikeln (z.B. Reißverschluss seitlich am Saum, Brusttasche, Volants, Netzeinsatz, Kragen in Kontrastfarbe)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'passform',
                'eightselectAttributeLabel' => 'PASSFORM',
                'eightselectAttributeLabelDescr' => 'in Bezug auf die Körperform, wird häufig für  Hemden, Sakkos und Anzüge verwendet (z.B. schmal, bequeme Weite, slim-fit, regular-fit, comfort-fit, körpernah)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'schnitt',
                'eightselectAttributeLabel' => 'SCHNITT',
                'eightselectAttributeLabelDescr' => 'in Bezug auf die Form des Artikels  (z.B. Bootcut, gerades Bein, Oversized, spitzer Schuh)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'aermellaenge',
                'eightselectAttributeLabel' => 'ÄRMELLÄNGE',
                'eightselectAttributeLabelDescr' => 'speziell bei Oberbekleidung: Länge der Ärmel (z.B. normal, extra-lange Ärmel, ärmellos, 3/4 Arm)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'kragenform',
                'eightselectAttributeLabel' => 'KRAGENFORM',
                'eightselectAttributeLabelDescr' => 'speziell bei Oberbekleidung: Beschreibung des Kragens  oder Ausschnitts (z.B. Rollkragen, V-Ausschnitt, Blusenkragen, Haifischkragen)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'verschluss',
                'eightselectAttributeLabel' => 'VERSCHLUSS',
                'eightselectAttributeLabelDescr' => 'beschreibt Verschlussarten (z.B: geknöpft, Reißverschluss,  Druckknöpfe, Klettverschluss; Haken&Öse)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'obermaterial',
                'eightselectAttributeLabel' => 'ART OBERMATERIAL',
                'eightselectAttributeLabelDescr' => 'wesentliches Material des Artikels (z.B. Wildleder, Denim,  Edelstahl, Gewebe, Strick, Jersey, Sweat, Crash)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'material',
                'eightselectAttributeLabel' => 'MATERIAL',
                'eightselectAttributeLabelDescr' => 'bezeichnet die genaue Materialzusammensetzung (z.B. 98% Baumwolle, 2% Elasthan)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'funktion',
                'eightselectAttributeLabel' => 'FUNKTION',
                'eightselectAttributeLabelDescr' => 'beschreibt Materialfunktionen und -eigenschaften (z.b. schnelltrocknend, atmungsaktiv, 100% UV-Schutz; pflegeleicht, bügelleicht, körperformend)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'eigenschaft',
                'eightselectAttributeLabel' => 'EIGENSCHAFT / EINSATZBEREICH',
                'eightselectAttributeLabelDescr' => 'speziell für Sport und Outdoor. Hinweise zum Einsatzbereich (Bsp. Schlafsack geeignet für Temparaturbereich 1 °C bis -16 °C, kratzfest, wasserdicht)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'auspraegung',
                'eightselectAttributeLabel' => 'AUSFÜHRUNG & MAßANGABEN',
                'eightselectAttributeLabelDescr' => 'speziell für Sport und Outdoor. Wichtige Informationen,  die helfen, den Artikel in das Sortiment einzuordnen (Beispiele: bei Rucksäcken: Volumen "30-55 Liter"; bei Skistöcken: Größenangaben in Maßeinheit "Körpergröße 160 bis 175cm";  Sonderausführungen: "Linkshänder")',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'fuellmenge',
                'eightselectAttributeLabel' => 'FUELLMENGE',
                'eightselectAttributeLabelDescr' => 'bezieht sich auf die Menge des Inhalts des Artikels (z.B. 200ml; 0,5 Liter, 3kg, 150 Stück)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'absatzhoehe',
                'eightselectAttributeLabel' => 'ABSATZHÖHE',
                'eightselectAttributeLabelDescr' => 'speziell bei Schuhen: Höhe des Absatzes (Format mit oder ohne Maßeinheit z.B. 5,5 cm oder 5,5)',
                'shopwareAttribute' => '-',
            ],
            [
                'eightselectAttribute' => 'sonstiges',
                'eightselectAttributeLabel' => 'SONSTIGES',
                'eightselectAttributeLabelDescr' => 'zusätzliche Artikelinformationen, die keinem spezifischen Attribut zugeordnet werden können',
                'shopwareAttribute' => '-',
            ],
        ];

        foreach ($attributeList as $attributeEntry) {
            $sql = 'INSERT INTO 8s_attribute_mapping (eightselectAttribute, eightselectAttributeLabel, eightselectAttributeLabelDescr, shopwareAttribute) VALUES (?, ?, ?, ?)';
            Shopware()->Db()->query(
                $sql,
                [
                    $attributeEntry['eightselectAttribute'],
                    $attributeEntry['eightselectAttributeLabel'],
                    $attributeEntry['eightselectAttributeLabelDescr'],
                    $attributeEntry['shopwareAttribute'],
                ]
            );
        }
    }

    /**
     * @throws \Exception
     * @return \DateTime
     */
    private function getNextMidnight()
    {
        $date = new \DateTime();
        $date->setTime(0, 0);
        $date->add(new \DateInterval('P1D'));

        return $date;
    }

    public function onBackendConfigSave()
    {
        // @todo config loggen
        /** @var $cacheManager \Shopware\Components\CacheManager */
        $cacheManager = $this->container->get('shopware.cache_manager');
        $cacheManager->clearConfigCache();
    }

    private function deleteExportDir()
    {
        $this->rrmdir(Shopware()->DocPath('files_8select'));
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
}
