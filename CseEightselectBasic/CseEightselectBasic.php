<?php

namespace CseEightselectBasic;

use CseEightselectBasic\Models\EightselectAttribute;
use CseEightselectBasic\Services\Dependencies\Provider;
use CseEightselectBasic\Services\Export\Connector;
use CseEightselectBasic\Services\Export\StatusExportDelta;
use CseEightselectBasic\Services\PluginConfig\PluginConfig as PluginConfigService;
use CseEightselectBasic\Setup\Helpers\EmotionComponents;
use CseEightselectBasic\Setup\Helpers\Logger;
use CseEightselectBasic\Setup\Helpers\MenuEntry;
use CseEightselectBasic\Setup\Install;
use CseEightselectBasic\Setup\Uninstall;
use CseEightselectBasic\Setup\Updates\Update_1_11_0;
use CseEightselectBasic\Setup\Updates\Update_2_0_0;
use CseEightselectBasic\Setup\Updates\Update_3_1_0;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\SchemaTool;
use Shopware;
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
    private $logMessages = [];

    /**
     * PluginConfigService
     */
    private $pluginConfigService;

    /**
     * integer
     */
    private $numberOfConfigElementsSaved = 0;

    /**
     * integer
     */
    private $numberOfConfigElements;

    /**
     * bool
     */
    private $hasLogError = false;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Theme_Compiler_Collect_Plugin_Javascript' => 'addJsFiles',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_CseEightselectBasic' => 'onGetFrontendCseEightselectBasicController',
            'Enlight_Controller_Action_PostDispatch_Backend_Index' => 'onPostDispatchBackendIndex',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Emotion' => 'onPostDispatchBackendEmotion',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onFrontendPostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Widgets_Emotion' => 'onFrontendPostDispatch',
            'Enlight_Controller_Action_PostDispatch_Frontend_Checkout' => 'onCheckoutConfirm',
            'Shopware_Controllers_Backend_Config_After_Save_Config_Element' => 'onBackendConfigSave',
        ];
    }

    private function getPluginConfigService()
    {
        if (!$this->container->has('cse_eightselect_basic.plugin_config.plugin_config')) {
            $configReader = $this->container->get('shopware.plugin.cached_config_reader');
            $configWriter = $this->container->get('config_writer');
            $pluginConfigService = new PluginConfigService(
                $this->container,
                $configReader,
                $configWriter,
                $this->getName()
            );
            $this->container->set('cse_eightselect_basic.plugin_config.plugin_config', $pluginConfigService);
        }

        return $this->container->get('cse_eightselect_basic.plugin_config.plugin_config');
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
        try {
            $result = $this->container->get('cse_eightselect_basic.config.validator')->validateWidgetConfig();
            $isCseWidgetConfigValid = $result['isValid'];

            $args->get('subject')->View()->assign('isCseWidgetConfigValid', $isCseWidgetConfigValid);

            $args->get('subject')->View()->assign(
                'htmlContainer',
                explode('CSE_SYS', $this->getPluginConfigService()->get('CseEightselectBasicSysPsvContainer'))
            );
            $args->get('subject')->View()->assign(
                'htmlSysAccContainer',
                explode('CSE_SYS', $this->getPluginConfigService()->get('CseEightselectBasicSysAccContainer'))
            );

            $args->get('subject')->View()->assign(
                'CseEightselectBasicApiId',
                $this->getPluginConfigService()->get('CseEightselectBasicApiId')
            );
            $args->get('subject')->View()->assign(
                'CseEightselectBasicPreviewActive',
                $this->getPluginConfigService()->get('CseEightselectBasicPreviewActive')
            );
            $args->get('subject')->View()->assign(
                'CseEightselectBasicSysAccActive',
                $this->getPluginConfigService()->get('CseEightselectBasicSysAccActive')
            );
            $args->get('subject')->View()->assign(
                'CseEightselectBasicSysPsvBlock',
                $this->getPluginConfigService()->get('CseEightselectBasicSysPsvBlock')
            );
            $args->get('subject')->View()->assign(
                'CseEightselectBasicSysPsvPosition',
                $this->getPluginConfigService()->get('CseEightselectBasicSysPsvPosition')
            );
            $args->get('subject')->View()->assign(
                'CseEightselectBasicCustomCss',
                $this->getPluginConfigService()->get('CseEightselectBasicCustomCss')
            );
            $args->get('subject')->View()->assign(
                'CseEightselectBasicSysPsvCssSelector',
                $this->getPluginConfigService()->get('CseEightselectBasicSysPsvCssSelector')
            );
        } catch (\Exception $exception) {
            $this->logException('onFrontendPostDispatch', $exception);
            $this->getCseLogger()->log('operation', $this->logMessages, $this->hasLogError);
        }
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatchBackendEmotion(\Enlight_Controller_ActionEventArgs $args)
    {
        try {
            $controller = $args->getSubject();
            $view = $controller->View();

            $view->addTemplateDir($this->getPath() . '/Resources/views/');
            $view->extendsTemplate('backend/emotion/model/translations.js');
            $view->extendsTemplate('backend/emotion/cse_eightselect_basic/view/detail/elements/sys_psv.js');
            $view->extendsTemplate('backend/emotion/cse_eightselect_basic/view/detail/elements/psp_psv.js');
            $view->extendsTemplate('backend/emotion/cse_eightselect_basic/view/detail/elements/psp_tlv.js');
        } catch (\Exception $exception) {
            $this->logException('onPostDispatchBackendEmotion', $exception);
            $this->getCseLogger()->log('operation', $this->logMessages, $this->hasLogError);
        }
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     *
     * @throws \Enlight_Exception
     */
    public function onPostDispatchBackendIndex(\Enlight_Controller_ActionEventArgs $args)
    {

        $controller = $args->getSubject();
        $view = $controller->View();

        try {
            if ($view->hasTemplate()) {
                $view->addTemplateDir($this->getPath() . '/Resources/views/');
                $view->extendsTemplate('backend/plugins/cse_eightselect_basic/index/header.tpl');
            }
        } catch (\Exception $exception) {
            $this->logException('onPostDispatchBackendIndex', $exception);
            $this->getCseLogger()->log('operation', $this->logMessages, $this->hasLogError);
        }
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     *
     * @throws \Enlight_Exception
     */
    public function onCheckoutConfirm(\Enlight_Event_EventArgs $args)
    {
        try {
            $request = $args->getRequest();
            $controller = $args->get('subject');
            $view = $controller->View();
            if ($request->getActionName() != 'finish') {
                return;
            }

            $result = $this->container->get('cse_eightselect_basic.config.validator')->validateWidgetConfig();
            $isCseWidgetConfigValid = $result['isValid'];

            if ($isCseWidgetConfigValid === false) {
                return;
            }
        } catch (\Exception $exception) {
            $this->logException('onCheckoutConfirm', $exception);
            $this->getCseLogger()->log('operation', $this->logMessages, $this->hasLogError);
        }
    }

    /**
     * @param InstallContext $context
     * @throws \Exception
     */
    public function install(InstallContext $context)
    {
        try {
            $this->logMessages[] = sprintf(
                'Install plugin %s',
                $context->getCurrentVersion()
            );

            $install = new Install(
                $context,
                new EmotionComponents($this->container->get('shopware.emotion_component_installer'), $this->getName()),
                new StatusExportDelta($this->container->get('dbal_connection')),
                new MenuEntry(
                    $this->container->get('dbal_connection'),
                    $this->container->get('shopware.plugin_manager')->getPluginByName('CseEightselectBasic')->getId()
                )
            );
            $install->execute();
            $this->logMessages[] = 'Plugin components installed';
            $this->getPluginConfigService()->setDefaults();
            $this->logMessages[] = 'PluginConfig defaults set';
            $this->logMessages[] = 'Plugin installation completed';
            $this->getCseLogger()->log('install', $this->logMessages, $this->hasLogError);
        } catch (\Exception $exception) {
            $this->logException('installation', $exception);
            $this->getCseLogger()->log('install', $this->logMessages, $this->hasLogError);

            throw $exception;
        }

        return parent::install($context);
    }

    /**
     * @param ActivateContext $context
     */
    public function activate(ActivateContext $context)
    {
        try {
            $this->logMessages[] = sprintf(
                'Activate plugin %s',
                $context->getCurrentVersion()
            );

            $this->connectCse();

            $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);

            $this->logMessages[] = 'Plugin activation completed';
            $this->getCseLogger()->log('activate', $this->logMessages, $this->hasLogError);
        } catch (\Exception $exception) {
            $this->logException('activation', $exception);
            $this->getCseLogger()->log('activate', $this->logMessages, $this->hasLogError);

            throw $exception;
        }
    }

    /**
     * @param DeactivateContext $context
     */
    public function deactivate(DeactivateContext $context)
    {
        try {
            $this->logMessages[] = sprintf(
                'Deactivate plugin %s',
                $context->getCurrentVersion()
            );

            $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);

            $this->logMessages[] = 'Plugin deactivation completed';
            $this->getCseLogger()->log('deactivate', $this->logMessages, $this->hasLogError);
        } catch (\Exception $exception) {
            $this->logException('deactivation', $exception);
            $this->getCseLogger()->log('deactivate', $this->logMessages, $this->hasLogError);

            throw $exception;
        }
    }

    /**
     * @param UpdateContext $context
     */
    public function update(UpdateContext $context)
    {
        try {
            $this->logMessages[] = sprintf(
                'Update plugin from %s to %s',
                $context->getCurrentVersion(),
                $context->getUpdateVersion()
            );

            switch (true) {
                case version_compare($context->getCurrentVersion(), '1.11.0', '<='):
                    $update = new Update_1_11_0(
                        $this->container->get('config'),
                        $this->container->get('config_writer'),
                        $this->getPluginConfigService()
                    );
                    $update->execute();
                    $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
                    $this->logMessages[] = 'Update_1_11_0 executed';
                // no break
                case version_compare($context->getCurrentVersion(), '2.0.0', '<'):
                    $update = new Update_2_0_0(
                        $this->container->get('dbal_connection'),
                        Shopware()->DocPath('files_8select')
                    );
                    $update->execute();
                    $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
                    $this->logMessages[] = 'Update_2_0_0 executed';
                // no break
                case version_compare($context->getCurrentVersion(), '3.1.0', '<'):
                    $update = new Update_3_1_0(
                        new MenuEntry(
                            $this->container->get('dbal_connection'),
                            $this->container->get('shopware.plugin_manager')->getPluginByName('CseEightselectBasic')->getId()
                        )
                    );
                    $update->execute();
                    $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
                    $this->logMessages[] = 'Update_3_1_0 executed';
                default:
                    $this->connectCse();
                    $context->scheduleClearCache(InstallContext::CACHE_LIST_FRONTEND);
                    $this->logMessages[] = 'Plugin update completed';
                    $this->getCseLogger()->log('update', $this->logMessages, $this->hasLogError);
            }
        } catch (\Exception $exception) {
            $this->logException('updating', $exception);
            $this->getCseLogger()->log('update', $this->logMessages, $this->hasLogError);

            throw $exception;
        }
    }

    /**
     * @param UninstallContext $context
     *
     * @throws \Exception
     */
    public function uninstall(UninstallContext $context)
    {
        try {
            $this->logMessages[] = sprintf(
                'Uninstall plugin %s',
                $context->getCurrentVersion()
            );

            $uninstall = new Uninstall(
                $context,
                new EmotionComponents($this->container->get('shopware.emotion_component_installer'), $this->getName()),
                new StatusExportDelta($this->container->get('dbal_connection')),
                new MenuEntry(
                    $this->container->get('dbal_connection'),
                    $this->container->get('shopware.plugin_manager')->getPluginByName('CseEightselectBasic')->getId()
                )
            );
            $uninstall->execute();
            $this->logMessages[] = 'Plugin components uninstalled';
            $this->removeDatabase();

            $context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);

            $this->logMessages[] = 'Plugin deinstallation completed';
            $this->getCseLogger()->log('uninstall', $this->logMessages, $this->hasLogError);
        } catch (\Exception $exception) {
            $this->logException('deinstallation', $exception);
            $this->getCseLogger()->log('uninstall', $this->logMessages, $this->hasLogError);

            throw $exception;
        }
    }

    private function updateSchema()
    {
        $modelManager = $this->container->get('models');
        $tool = new SchemaTool($modelManager);
        $classes = $this->getClasses($modelManager);
        $tool->updateSchema($classes, true);
        $this->logMessages[] = 'Database scheme updated';
    }

    private function dropSchema()
    {
        $modelManager = $this->container->get('models');
        $tool = new SchemaTool($modelManager);
        $classes = $this->getClasses($modelManager);
        $tool->dropSchema($classes);
        $this->logMessages[] = 'Database scheme dropped';
    }

    private function removeDatabase()
    {
        $this->dropSchema();
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
     * Provide the file collection for js files.
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
     * this is invoked for each config element, see https://github.com/shopware/shopware/blob/5.2/engine/Shopware/Controllers/Backend/Config.php#L1270
     * @param \Enlight_Event_EventArgs $args
     */
    public function onBackendConfigSave(\Enlight_Event_EventArgs $args)
    {
        try {
            $this->updateCachedPluginConfig($args);

            $this->numberOfConfigElementsSaved++;
            if (!$this->isConfigSaveComplete($args)) {
                return;
            }

            /** @var $cacheManager \Shopware\Components\CacheManager */
            $cacheManager = $this->container->get('shopware.cache_manager');
            $cacheManager->clearConfigCache();
            $cacheManager->clearTemplateCache();

            $this->connectCse();
            $this->getCseLogger()->log('pluginconfig', $this->logMessages, $this->hasLogError);
        } catch (\Exception $exception) {
            $this->logException('saving plugin configuration', $exception);
            $this->getCseLogger()->log('pluginconfig', $this->logMessages, $this->hasLogError);
        }
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    private function isConfigSaveComplete(\Enlight_Event_EventArgs $args)
    {
        try {
            if (!isset($this->numberOfConfigElements)) {
                $formElement = $args->get('element');
                $querybuilder = Shopware()->Models()->createQueryBuilder();
                $querybuilder
                    ->select('count(configelement.id)')
                    ->from('Shopware\Models\Config\Element', 'configelement')
                    ->where('configelement.form = :form_id')
                    ->setParameter('form_id', $formElement->getForm()->getId());
                $this->numberOfConfigElements = $querybuilder->getQuery()->getSingleScalarResult();
            }

            return $this->numberOfConfigElementsSaved >= $this->numberOfConfigElements;
        } catch (\Exception $exception) {
            $this->logException('saving plugin configuration', $exception);

            return true;
        }
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    private function updateCachedPluginConfig(\Enlight_Event_EventArgs $args)
    {
        $elementsToUpdate = [
            'CseEightselectBasicActiveShopId' => true,
            'CseEightselectBasicApiId' => true,
            'CseEightselectBasicFeedId' => true,
        ];

        $formElement = $args->get('element');
        $name = $formElement->getName();

        if (!isset($elementsToUpdate[$name])) {
            return;
        }

        $value = $formElement->getValues()->current();
        if ($value === false) {
            $this->getPluginConfigService()->set($name, null);
        } else {
            $this->getPluginConfigService()->set($name, $value->getValue());
        }
    }

    /**
     * @throws \Exception
     */
    private function connectCse()
    {
        try {
            $this->getCseConnector()->connect();
            $this->logMessages[] = 'Connected to CSE';

            return;
        } catch (\Exception $exception) {
            $this->logException('connecting to cse', $exception);
        }
    }

    /**
     * @return Connector
     */
    private function getCseConnector()
    {
        if ($this->container->has('cse_eightselect_basic.export.connector')) {
            return $this->container->get('cse_eightselect_basic.export.connector');
        }

        $guzzleFactory = $this->container->get('guzzle_http_client_factory');
        $provider = new Provider($this->container, $this->getPluginConfigService());
        $cseConnector = new Connector(
            $guzzleFactory,
            $this->getPluginConfigService(),
            $provider
        );
        $this->container->set('cse_eightselect_basic.export.connector', $cseConnector);

        return $cseConnector;
    }

    /**
     * @return Logger
     */
    private function getCseLogger()
    {
        if ($this->container->has('cse_eightselect_basic.setup.helpers.logger')) {
            return $this->container->get('cse_eightselect_basic.setup.helpers.logger');
        }

        $guzzleFactory = $this->container->get('guzzle_http_client_factory');
        $provider = new Provider($this->container, $this->getPluginConfigService());
        $cseLogger = new Logger(
            $guzzleFactory,
            $this->getPluginConfigService(),
            $provider
        );
        $this->container->set('cse_eightselect_basic.setup.helpers.logger', $cseLogger);

        return $cseLogger;
    }

    /**
     * @param string action
     * @param \Exception $exception
     */
    private function logException($action, $exception)
    {
        $this->hasLogError = true;
        $message = sprintf('%s failed due to exception: %s', $action, $exception->getMessage());
        $context = [
            'exception' => [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ],
        ];
        $this->logMessages[] = [
            'message' => $message,
            'context' => $context,
        ];
        $this->container->get('pluginlogger')->error($message, $context);
    }
}
