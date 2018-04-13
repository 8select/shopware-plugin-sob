<?php
namespace EightSelect;

use Shopware\Components\Emotion\ComponentInstaller;
use Shopware\Components\Plugin;
use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Model\ModelManager;
use Doctrine\ORM\Tools\SchemaTool;
use EightSelect\Models\EightSelectAttribute;
use Shopware\Components\Plugin\Context\UpdateContext;

class EightSelect extends Plugin
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch'                             => 'onPreDispatch',
            'Theme_Compiler_Collect_Plugin_Javascript'                          => 'addJsFiles',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_EightSelect' => 'onGetFrontendEightSelectController',
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_EightSelect'  => 'onGetBackendEightSelectController',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Emotion'      => 'onPostDispatchBackendEmotion',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend'             => 'onFrontendPostDispatch',
            'Enlight_Controller_Action_PostDispatch_Frontend_Checkout'          => 'onCheckoutConfirm',
            'Shopware_Console_Add_Command'                                      => 'onStartDispatch',
            'Shopware_CronJob_EightSelectArticleExport'                         => 'eightSelectArticleExport',
            'Shopware_CronJob_EightSelectQuickUpdate'                           => 'eightSelectQuickUpdate',
        ];
    }

    public function onPreDispatch()
    {
        Shopware()->Template()->addTemplateDir($this->getPath() . '/Resources/views/');
    }

    /**
     * @return string
     */
    public function onGetBackendEightSelectController()
    {
        return $this->getPath() . '/Controllers/Backend/EightSelect.php';
    }

    /**
     * @return string
     */
    public function onGetFrontendEightSelectController()
    {
        return $this->getPath() . '/Controllers/Frontend/EightSelect.php';
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return string
     */
    public function getVersion()
    {
        return Shopware()->Db()->query(
            'SELECT version FROM s_core_plugins WHERE name = ?',
            [$this->getName()]
        )->fetchColumn();
    }

    public function onStartDispatch()
    {
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            require_once __DIR__ . '/vendor/autoload.php';
        }
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onFrontendPostDispatch(\Enlight_Event_EventArgs $args)
    {
        $config = Shopware()->Config();

        if (!$config->get('8s_enabled')) {
            return;
        }

        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $view = $controller->View();

        $htmlContainer = $config->get('8s_html_container_element');
        $view->assign('htmlContainer', explode('CSE_SYS', $htmlContainer));
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
        $view->extendsTemplate('backend/emotion/eight_select/view/detail/elements/sys_psv.js');
        $view->extendsTemplate('backend/emotion/eight_select/view/detail/elements/psp_psv.js');
        $view->extendsTemplate('backend/emotion/eight_select/view/detail/elements/psp_tlv.js');
    }

    /**
     * @param  \Enlight_Event_EventArgs $args
     * @throws \Enlight_Exception
     */
    public function onCheckoutConfirm(\Enlight_Event_EventArgs $args)
    {
        $config = Shopware()->Config();

        if (!$config->get('8s_enabled')) {
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
     */
    public function install(InstallContext $context)
    {
        $this->addExportCron();
        $this->addUpdateCron();
        $this->installWidgets();
        $this->createDatabase();
        parent::install($context);
    }

    /**
     * @param ActivateContext $activateContext
     */
    public function activate(ActivateContext $activateContext)
    {
        $activateContext->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }

    /**
     * @param UpdateContext $context
     */
    public function update(UpdateContext $context)
    {
        parent::update($context);

        switch ($context->getCurrentVersion()) {
            case '1.0.0':
                $this->update_110();
        }
    }

    /**
     * Update to Version 1.1.0
     */
    private function update_110()
    {
        $this->createDatabase();
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
                'name'     => 'SYS-PSV Component',
                'template' => 'sys_psv',
                'cls'      => '8select--element--sys-psv',
                'xtype'    => 'emotion-8select-syspsv-element',
            ]
        );
        $syspsvElement->createHiddenField(
            [
                'name'       => 'sys_psv_ordernumber',
                'fieldLabel' => 'Product Ordernumber',
                'allowBlank' => false,
            ]
        );
        $syspsvElement->createNumberField(
            [
                'name'         => 'sys_psv_lazyload_factor',
                'fieldLabel'   => 'Lazy Load Distance Factor',
                'defaultValue' => 0,
                'helpText'     => 'Definiert einen Faktor auf Basis der Fensterhöhe, ab dem das Widget unterhalb des 
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
                'name'     => 'PSP-TLV Component',
                'template' => 'psp_tlv',
                'cls'      => '8select--element--psp-tlv',
                'xtype'    => 'emotion-8select-psptlv-element',
            ]
        );
        $psptlvElement->createTextField(
            [
                'name'       => 'psp_tlv_stylefactor',
                'fieldLabel' => 'Stylefactor',
                'allowBlank' => false,
            ]
        );
        $psptlvElement->createNumberField(
            [
                'name'         => 'psp_tlv_lazyload_factor',
                'fieldLabel'   => 'Lazy Load Distance Factor',
                'defaultValue' => 0,
                'helpText'     => 'Definiert einen Faktor auf Basis der Fensterhöhe, ab dem das Widget unterhalb des
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
                'name'     => 'PSP-PSV Component',
                'template' => 'psp_psv',
                'cls'      => '8select--element--psp-psv',
                'xtype'    => 'emotion-8select-psppsv-element',
            ]
        );
        $psppsvElement->createTextField(
            [
                'name'       => 'psp_psv_set_id',
                'fieldLabel' => 'Set-ID',
                'allowBlank' => false,
            ]
        );
        $psppsvElement->createNumberField(
            [
                'name'         => 'psp_psv_lazyload_factor',
                'fieldLabel'   => 'Lazy Load Distance Factor',
                'defaultValue' => 0,
                'helpText'     => 'Definiert einen Faktor auf Basis der Fensterhöhe, ab dem das Widget unterhalb des
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
     */
    public function uninstall(UninstallContext $context)
    {
        $this->removeExportCron();
        $this->removeUpdateCron();
        $this->removeDatabase();
        parent::uninstall($context);
    }

    private function createDatabase()
    {
        $modelManager = $this->container->get('models');
        $tool = new SchemaTool($modelManager);

        $classes = $this->getClasses($modelManager);

        $tool->updateSchema($classes, true); // make sure to use the save mode

        $this->initAttributes();
        $this->initChangesQueueTable();
    }

    private function removeDatabase()
    {
        $modelManager = $this->container->get('models');
        $tool = new SchemaTool($modelManager);

        $classes = $this->getClasses($modelManager);

        $tool->dropSchema($classes);
    }

    /**
     * @param  ModelManager $modelManager
     * @return array
     */
    private function getClasses(ModelManager $modelManager)
    {
        return [
            $modelManager->getClassMetadata(EightSelectAttribute::class),
        ];
    }

    /**
     * @param \Shopware_Components_Cron_CronJob $job
     */
    public function eightSelectArticleExport(\Shopware_Components_Cron_CronJob $job)
    {
        $this->container->get('eight_select.article_export')->doCron();
    }

    /**
     * @param \Shopware_Components_Cron_CronJob $job
     */
    public function eightSelectQuickUpdate(\Shopware_Components_Cron_CronJob $job)
    {
        $this->container->get('eight_select.quick_update')->doCron();
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
     * add cron job for exporting all products
     */
    public function addExportCron()
    {
        $connection = $this->container->get('dbal_connection');
        $connection->insert(
            's_crontab',
            [
                'name'       => '8select article export',
                'action'     => 'Shopware_CronJob_EightSelectArticleExport',
                'next'       => $this->getNextMidnight(),
                'start'      => null,
                '`interval`' => '86400',
                'active'     => 1,
                'end'        => new \DateTime(),
                'pluginID'   => $this->container->get('shopware.plugin_manager')->getPluginByName($this->getName())->getId(),
            ],
            [
                'next' => 'datetime',
                'end'  => 'datetime',
            ]
        );
    }

    public function removeExportCron()
    {
        $this->container->get('dbal_connection')->executeQuery('DELETE FROM s_crontab WHERE `action` = ?', [
            'Shopware_CronJob_EightSelectArticleExport',
        ]);
    }

    /**
     * add cron job for exporting all products
     */
    public function addUpdateCron()
    {
        $connection = $this->container->get('dbal_connection');
        $connection->insert(
            's_crontab',
            [
                'name'       => '8select quick product update',
                'action'     => 'Shopware_CronJob_EightSelectQuickUpdate',
                'next'       => null,
                'start'      => null,
                '`interval`' => '60',
                'active'     => 1,
                'end'        => new \DateTime(),
                'pluginID'   => $this->container->get('shopware.plugin_manager')->getPluginByName($this->getName())->getId(),
            ],
            [
                'next' => 'datetime',
                'end'  => 'datetime',
            ]
        );
    }

    public function removeUpdateCron()
    {
        $this->container->get('dbal_connection')->executeQuery('DELETE FROM s_crontab WHERE `action` = ?', [
            'Shopware_CronJob_EightSelectQuickUpdate',
        ]);
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     */
    private function initAttributes()
    {
        $attributeList = [
            [
                'eightSelectAttribute' => 'sku',
                'shopwareAttribute'    => 's_articles_details.ordernumber',
            ],
            [
                'eightSelectAttribute' => 'mastersku',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'ean',
                'shopwareAttribute'    => 's_articles_details.ean',
            ],
            [
                'eightSelectAttribute' => 'model',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'name1',
                'shopwareAttribute'    => 's_articles.name',
            ],
            [
                'eightSelectAttribute' => 'name2',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'groesse',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'bereich',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'rubrik',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'abteilung',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'kiko',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'typ',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'farbe',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'farbspektrum',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'absatzhoehe',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'muster',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'aermellaenge',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'kragenform',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'obermaterial',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'passform',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'schnitt',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'waschung',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'stil',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'sportart',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'detail',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'auspraegung',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'baukasten',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'eigenschaft',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'fuellmenge',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'funktion',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'gruppe',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'material',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'saison',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'serie',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'verschluss',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'beschreibung',
                'shopwareAttribute'    => 's_articles.description',
            ],
            [
                'eightSelectAttribute' => 'beschreibung1',
                'shopwareAttribute'    => 's_articles.description_long',
            ],
            [
                'eightSelectAttribute' => 'beschreibung2',
                'shopwareAttribute'    => '-',
            ],
            [
                'eightSelectAttribute' => 'sonstiges',
                'shopwareAttribute'    => '-',
            ],
        ];

        foreach ($attributeList as $attributeEntry) {
            $sql = 'INSERT INTO 8s_attribute_mapping (eightSelectAttribute, shopwareAttribute) VALUES (?, ?)';
            Shopware()->Db()->query(
                $sql,
                [$attributeEntry['eightSelectAttribute'], $attributeEntry['shopwareAttribute']]
            );
        }
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     */
    private function initChangesQueueTable()
    {
        $triggerSqls = [
            'DROP TABLE IF EXISTS `8s_articles_details_change_queue`;',
            'CREATE TABLE `8s_articles_details_change_queue` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `s_articles_details_id` int(11) NOT NULL,
                  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`)
                ) COLLATE=\'utf8_unicode_ci\' ENGINE=InnoDB DEFAULT CHARSET=utf8;',
            'DROP TRIGGER IF EXISTS `8s_articles_details_change_queue_writer`',
            'CREATE TRIGGER `8s_articles_details_change_queue_writer` AFTER UPDATE on `s_articles_details`
                  FOR EACH ROW
                  BEGIN
                    IF (NEW.instock != OLD.instock OR NEW.active != OLD.active) THEN
                      INSERT INTO 8s_articles_details_change_queue (s_articles_details_id) VALUES (NEW.id);
                    END IF;
                  END',
            'DROP TRIGGER IF EXISTS `8s_s_articles_prices_change_queue_writer`',
            'CREATE TRIGGER `8s_s_articles_prices_change_queue_writer` AFTER UPDATE on `s_articles_prices`
                  FOR EACH ROW
                  BEGIN
                    IF (NEW.price != OLD.price OR NEW.pseudoprice != OLD.pseudoprice) THEN
                      INSERT INTO 8s_articles_details_change_queue (s_articles_details_id) VALUES (NEW.articleDetailsID);
                    END IF;
                  END',
        ];

        foreach ($triggerSqls as $triggerSql) {
            Shopware()->Db()->query($triggerSql);
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
}
