<?php

namespace CseEightselectBasic;

use CseEightselectBasic\Components\ExportSetup;
use CseEightselectBasic\Components\FeedLogger;
use CseEightselectBasic\Components\RunCronOnce;
use CseEightselectBasic\Models\EightselectAttribute;
use CseEightselectBasic\Services\Config\Config;
use CseEightselectBasic\Services\Dependencies\Provider;
use CseEightselectBasic\Services\PluginConfig\PluginConfig as PluginConfigService;
use CseEightselectBasic\Setup\Helpers\AttributeMapping;
use CseEightselectBasic\Setup\Updates\Update_1_11_0;
use CseEightselectBasic\Setup\Updates\Update_1_11_1;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\SchemaTool;
use Shopware;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
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
        $provider = new Provider($this->container, $this->getPluginConfigService());
        $currentShop = $provider->getCurrentShop();
        $shopUrl = $currentShop->getHost() . $currentShop->getBaseUrl() . $currentShop->getBasePath();

        try {
            $this->installMessages[] = 'Shop-URL: ' . $shopUrl;
            $this->installMessages[] = 'Shopware-Version: ' . $this->container->get('shopware.release')->getVersion();
            $this->installMessages[] = 'CSE-Plugin-Version: ' . $context->getCurrentVersion();
        } catch (\Exception $exception) {
            $this->installMessages[] = 'ERROR: initInstallLog ' . (string) $exception;
        }
    }

    private function sendLog($type = 'install')
    {
        $logMessage = implode(\PHP_EOL . \PHP_EOL, $this->installMessages);
        Shopware()->PluginLogger()->info($logMessage);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Theme_Compiler_Collect_Plugin_Javascript' => 'addJsFiles',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_CseEightselectBasic' => 'onGetFrontendCseEightselectBasicController',
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_CseEightselectBasic' => 'onGetBackendCseEightselectBasicController',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Emotion' => 'onPostDispatchBackendEmotion',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onFrontendPostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Widgets_Emotion' => 'onFrontendPostDispatch',
            'Enlight_Controller_Action_PostDispatch_Frontend_Checkout' => 'onCheckoutConfirm',
            'Shopware_Controllers_Backend_Config_After_Save_Config_Element' => 'onBackendConfigSave',
        ];
    }

    private function getPluginConfigService()
    {
        if (!isset($this->pluginConfigService)) {
            $configReader = $this->container->get('shopware.plugin.cached_config_reader');
            $configWriter = $this->container->get('config_writer');
            $this->pluginConfigService = new PluginConfigService(
                $this->container,
                $configReader,
                $configWriter,
                $this->getName()
            );
        }

        return $this->pluginConfigService;
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
     *
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
        $isCseWidgetConfigValid = $this->container->get('cse_eightselect_basic.config.validator')->validateWidgetConfig()['isValid'];

        $args->get('subject')->View()->assign('isCseWidgetConfigValid', $isCseWidgetConfigValid);

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
     *
     * @throws \Enlight_Exception
     */
    public function onCheckoutConfirm(\Enlight_Event_EventArgs $args)
    {
        if ($this->container->get('cse_eightselect_basic.config.validator')->validateWidgetConfig()['isValid'] !== true) {
            return;
        }

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
        $install = new Install(
            $context,
            new SizeAttribute(
                $this->container->get('shopware_attribute.crud_service'),
                Shopware()->Models()->getConfiguration()->getMetadataCacheImpl(),
                Shopware()->Models()
            ),
            new EmotionComponents($this->container->get('shopware.emotion_component_installer'))
        );
        $install->execute();
        $this->createDatabase($context);
        $this->getPluginConfigService()->setDefaults();

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
            case version_compare($context->getCurrentVersion(), '1.6.3', '<='):
                $this->update_1_6_3();
            case version_compare($context->getCurrentVersion(), '1.8.0', '<='):
                $this->update_1_8_0();
            case version_compare($context->getCurrentVersion(), '1.11.0', '<='):
                $update = new Update_1_11_0(
                    $this->container->get('config'),
                    $this->container->get('config_writer'),
                    $this->getPluginConfigService()
                );
                $update->execute();
            case version_compare($context->getCurrentVersion(), '1.11.1', '<='):
                $update = new Update_1_11_1(
                    $this->container->get('dbal_connection'),
                    Shopware()->DocPath('files_8select')
                );
                $update->execute();
        }

        $this->installMessages[] = 'Update auf CSE-Plugin-Version: ' . $context->getUpdateVersion();
        $this->sendLog('update');

        return $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }

    private function update_1_6_3()
    {
        // update changeQueue table and triggers
        ExportSetup::dropChangeQueueTriggers();
        ExportSetup::dropChangeQueueTable();
        ExportSetup::createChangeQueueTable();
        ExportSetup::createChangeQueueTriggers();
    }

    private function update_1_8_0()
    {
        $this->createDefaultConfig();
    }

    /**
     * @param UninstallContext $context
     *
     * @throws \Exception
     */
    public function uninstall(UninstallContext $context)
    {
        $this->initInstallLog($context);

        $uninstall = new Uninstall(
            $context,
            new SizeAttribute(
                $this->container->get('shopware_attribute.crud_service'),
                Shopware()->Models()->getConfiguration()->getMetadataCacheImpl(),
                Shopware()->Models()
            ),
            new EmotionComponents($this->container->get('shopware.emotion_component_installer'))
        );
        $uninstall->execute();
        $this->removeDatabase();

        $this->sendLog('uninstall');

        $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
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
        $attributeMapping = new AttributeMapping(Shopware()->Db());
        $attributeMapping->initAttributes();
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
        if ($this->getPluginConfigService()->isCseActive() === false) {
            return;
        }

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
        if ($this->getPluginConfigService()->isCseActive() === false) {
            return;
        }

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
        if ($this->getPluginConfigService()->isCseActive() === false) {
            return;
        }

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
        if ($this->getPluginConfigService()->isCseActive() === false) {
            return;
        }

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
     *
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
}
