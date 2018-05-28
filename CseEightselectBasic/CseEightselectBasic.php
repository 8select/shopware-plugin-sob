<?php
namespace CseEightselectBasic;

use Shopware\Components\Emotion\ComponentInstaller;
use Shopware\Components\Plugin;
use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Model\ModelManager;
use Doctrine\ORM\Tools\SchemaTool;
use CseEightselectBasic\Models\EightselectAttribute;
use CseEightselectBasic\Components\ArticleExport;
use Shopware\Components\Plugin\Context\UpdateContext;

class CseEightselectBasic extends Plugin
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch'                                         => 'onPreDispatch',
            'Theme_Compiler_Collect_Plugin_Javascript'                                      => 'addJsFiles',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_CseEightselectBasic'     => 'onGetFrontendCseEightselectBasicController',
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_CseEightselectBasic'      => 'onGetBackendCseEightselectBasicController',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Emotion'                  => 'onPostDispatchBackendEmotion',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend'                         => 'onFrontendPostDispatch',
            'Enlight_Controller_Action_PostDispatch_Frontend_Checkout'                      => 'onCheckoutConfirm',
            'Shopware_CronJob_CseEightselectBasicArticleExport'                             => 'cseEightselectBasicArticleExport',
            'Shopware_CronJob_CseEightselectBasicArticleExportOnce'                         => 'cseEightselectBasicArticleExportOnce',
            'Shopware_CronJob_CseEightselectBasicQuickUpdate'                               => 'cseEightselectBasicQuickUpdate',
            'Shopware_Controllers_Backend_Config_After_Save_Config_Element'                 => 'onBackendConfigSave',
        ];
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
        $view->extendsTemplate('backend/emotion/cse_eightselect_basic/view/detail/elements/sys_psv.js');
        $view->extendsTemplate('backend/emotion/cse_eightselect_basic/view/detail/elements/psp_psv.js');
        $view->extendsTemplate('backend/emotion/cse_eightselect_basic/view/detail/elements/psp_tlv.js');
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
     * @param  InstallContext $context
     * @throws \Exception
     */
    public function install(InstallContext $context)
    {
        $this->addExportCron();
        $this->addExportOnceCron();
        $this->addUpdateCron();
        $this->installWidgets();
        $this->createDatabase();
        $this->createExportDir();
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
            case '1.0.2':
                $this->update_1_0_2();
        }
    }

    /**
     * Update to Version 1.0.2
     */
    private function update_1_0_2()
    {
        $this->deleteExportDir();
        $this->createExportDir();
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
        $this->removeExportOnceCron();
        $this->removeUpdateCron();
        $this->removeDatabase();
        $this->deleteExportDir();
        parent::uninstall($context);
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     */
    private function createDatabase()
    {
        $modelManager = $this->container->get('models');
        $tool = new SchemaTool($modelManager);

        $classes = $this->getClasses($modelManager);

        $tool->updateSchema($classes, true); // make sure to use the save mode

        $this->initAttributes();
        $this->initChangesQueueTable();
        $this->initRunOnceQueueTable();
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
            $modelManager->getClassMetadata(EightselectAttribute::class),
        ];
    }

    /**
     * @param  \Shopware_Components_Cron_CronJob $job
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public function cseEightselectBasicArticleExport(\Shopware_Components_Cron_CronJob $job)
    {
        $this->container->get('cse_eightselect_basic.article_export')->doCron();
    }

    /**
     * @param  \Shopware_Components_Cron_CronJob $job
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public function cseEightselectBasicArticleExportOnce(\Shopware_Components_Cron_CronJob $job)
    {
        $this->container->get('cse_eightselect_basic.article_export')->checkRunOnce();
    }

    /**
     * @param  \Shopware_Components_Cron_CronJob $job
     * @throws \Exception
     */
    public function cseEightselectBasicQuickUpdate(\Shopware_Components_Cron_CronJob $job)
    {
        $this->container->get('cse_eightselect_basic.quick_update')->doCron();
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
                'name'       => '8select article export',
                'action'     => 'Shopware_CronJob_CseEightselectBasicArticleExport',
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
                'name'       => '8select article export once',
                'action'     => 'Shopware_CronJob_CseEightselectBasicArticleExportOnce',
                'next'       => new \DateTime(),
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

    public function removeExportOnceCron()
    {
        $this->container->get('dbal_connection')->executeQuery('DELETE FROM s_crontab WHERE `action` = ?', [
            'Shopware_CronJob_CseEightselectBasicArticleExportOnce',
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
                'action'     => 'Shopware_CronJob_CseEightselectBasicQuickUpdate',
                'next'       => new \DateTime(),
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
            'Shopware_CronJob_CseEightselectBasicQuickUpdate',
        ]);
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     */
    private function initAttributes()
    {
        $attributeList = [
            [
                'eightselectAttribute'           => 'ean',
                'eightselectAttributeLabel'      => 'EAN Nummer',
                'eightselectAttributeLabelDescr' => 'z.B. "8698272518204"',
                'shopwareAttribute'              => 's_articles_details.ean',
            ],
            [
                'eightselectAttribute'           => 'model',
                'eightselectAttributeLabel'      => 'Master-Modell',
                'eightselectAttributeLabelDescr' => 'Artnr. Master-Modell bei mehreren Farben und Größen z.B. "efw34t63g4h"',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'name1',
                'eightselectAttributeLabel'      => 'Artikelbezeichnung',
                'eightselectAttributeLabelDescr' => 'z.B. "Hemdbluse Sabine", "Casual Friday Sakko", etc.',
                'shopwareAttribute'              => 's_articles.name',
            ],
            [
                'eightselectAttribute'           => 'name2',
                'eightselectAttributeLabel'      => 'Alternative Artikelbezeichnung',
                'eightselectAttributeLabelDescr' => 'z.B. "Bluse", "Sakko", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'groesse',
                'eightselectAttributeLabel'      => 'Größe',
                'eightselectAttributeLabelDescr' => 'z.B. "44", "S/31", "XL", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'bereich',
                'eightselectAttributeLabel'      => 'Bereich',
                'eightselectAttributeLabelDescr' => 'z.B. "Outdoor", "Mode", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'rubrik',
                'eightselectAttributeLabel'      => 'Produktkategorie',
                'eightselectAttributeLabelDescr' => 'z.B. "Jacken und Mäntel", "Blusen und Tuniken", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'abteilung',
                'eightselectAttributeLabel'      => 'Abteilung',
                'eightselectAttributeLabelDescr' => 'z.B. "haka", "dob", "kiko", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'kiko',
                'eightselectAttributeLabel'      => 'Kiko',
                'eightselectAttributeLabelDescr' => 'Abteilung Kindergrößen z.B. "maedchen", "jungen", "baby", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'typ',
                'eightselectAttributeLabel'      => 'Produkttyp',
                'eightselectAttributeLabelDescr' => 'z.B. "Lederjacken", "Regenhosen", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'farbe',
                'eightselectAttributeLabel'      => 'Produktfarbe',
                'eightselectAttributeLabelDescr' => 'z.B. "gelb", "schwarz/blau|rot|grün|Orange-Braun", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'farbspektrum',
                'eightselectAttributeLabel'      => 'Farbspektrum',
                'eightselectAttributeLabelDescr' => 'z.B. "Braun Beige", "Schwarz Grau", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'absatzhoehe',
                'eightselectAttributeLabel'      => 'Absatzhöhe',
                'eightselectAttributeLabelDescr' => 'z.B. "5,5 cm", "Absatzhöhe in mm: 60", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'muster',
                'eightselectAttributeLabel'      => 'Muster',
                'eightselectAttributeLabelDescr' => 'z.B. "kariert", "floral", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'aermellaenge',
                'eightselectAttributeLabel'      => 'Ärmellänge',
                'eightselectAttributeLabelDescr' => 'z.B. "extra kurzer Arm", "kurze Ärmel", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'kragenform',
                'eightselectAttributeLabel'      => 'Kragenform',
                'eightselectAttributeLabelDescr' => 'z.B. "Rundhalsausschnitt", "Blusenkragen", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'obermaterial',
                'eightselectAttributeLabel'      => 'Obermaterial',
                'eightselectAttributeLabelDescr' => 'z.B. "baumwoll-denim", "velourlederoptik", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'passform',
                'eightselectAttributeLabel'      => 'Passform',
                'eightselectAttributeLabelDescr' => 'z.B. "modern fit", "comfort fit", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'schnitt',
                'eightselectAttributeLabel'      => 'Schnitt',
                'eightselectAttributeLabelDescr' => 'z.B. "knielang", "7/8", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'waschung',
                'eightselectAttributeLabel'      => 'Waschung',
                'eightselectAttributeLabelDescr' => 'z.B. "bleached", "destroyed", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'stil',
                'eightselectAttributeLabel'      => 'Stil',
                'eightselectAttributeLabelDescr' => 'z.B. "Casual", "Business-Hemden", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'sportart',
                'eightselectAttributeLabel'      => 'Sportart',
                'eightselectAttributeLabelDescr' => 'z.B. "Bergsteigen", "Rennradfahren|Bike - Race", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'detail',
                'eightselectAttributeLabel'      => 'Produktdetail',
                'eightselectAttributeLabelDescr' => 'z.B. "Brusttasche", "Reißverschlüsse seitlich am Saum", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'auspraegung',
                'eightselectAttributeLabel'      => 'Ausprägung',
                'eightselectAttributeLabelDescr' => 'z.B. "30-55 Liter", "Rucksackmaße 40 cm x 28 cm x 18 cm", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'baukasten',
                'eightselectAttributeLabel'      => 'Baukasten',
                'eightselectAttributeLabelDescr' => 'z.B. "100760001"',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'eigenschaft',
                'eightselectAttributeLabel'      => 'Eigenschaft',
                'eightselectAttributeLabelDescr' => 'z.B. "5°C"',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'fuellmenge',
                'eightselectAttributeLabel'      => 'Füllmenge',
                'eightselectAttributeLabelDescr' => 'z.B. "200ml"',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'funktion',
                'eightselectAttributeLabel'      => 'Funktion',
                'eightselectAttributeLabelDescr' => 'z.B. "atmungsaktiv", "schnelltrocknend|trocknet schnell", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'gruppe',
                'eightselectAttributeLabel'      => 'Gruppe',
                'eightselectAttributeLabelDescr' => 'z.B. "Baukastenanzug James", "B-All-Mountain", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'material',
                'eightselectAttributeLabel'      => 'Material',
                'eightselectAttributeLabelDescr' => 'z.B. "100% Nylon (Ripstop) mit Gore-Tex-Membran (PTFE)", "100% Baumwolle", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'saison',
                'eightselectAttributeLabel'      => 'Saison',
                'eightselectAttributeLabelDescr' => 'z.B. "Winter 17", "Sommer 18", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'serie',
                'eightselectAttributeLabel'      => 'Serie',
                'eightselectAttributeLabelDescr' => 'z.B. "Mountain"',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'verschluss',
                'eightselectAttributeLabel'      => 'Verschluss',
                'eightselectAttributeLabelDescr' => 'z.B. "Knöpfe", "Reisverschluss", etc.',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'beschreibung',
                'eightselectAttributeLabel'      => 'Beschreibungstext (HTML)',
                'eightselectAttributeLabelDescr' => 'z.B. "<p>Federleichte Regenhose! </ br> ...</p>"',
                'shopwareAttribute'              => 's_articles.description_long',
            ],
            [
                'eightselectAttribute'           => 'beschreibung1',
                'eightselectAttributeLabel'      => 'Beschreibungstext (Text)',
                'eightselectAttributeLabelDescr' => 'z.B. "Federleichte Regenhose! ..."',
                'shopwareAttribute'              => 's_articles.description_long',
            ],
            [
                'eightselectAttribute'           => 'beschreibung2',
                'eightselectAttributeLabel'      => 'Beschreibung Zusatz',
                'eightselectAttributeLabelDescr' => 'z.B. "Gewicht=200g Gewogen=Gr. L/31 ..."',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'sonstiges',
                'eightselectAttributeLabel'      => 'Sonstige Beschreibungen / Attribute',
                'eightselectAttributeLabelDescr' => 'z.B. "abgenähte Taschen", "Ripstop"',
                'shopwareAttribute'              => '-',
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
     * @throws \Zend_Db_Adapter_Exception
     */
    private function initRunOnceQueueTable()
    {
        $sqls = [
            'DROP TABLE IF EXISTS `8s_cron_run_once_queue`;',
            'CREATE TABLE `8s_cron_run_once_queue` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `cron_name` varchar(255) NOT NULL,
                  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
                  `running` bit DEFAULT 0,
                  `progress` int(3) DEFAULT 0,
                  PRIMARY KEY (`id`)
                ) COLLATE=\'utf8_unicode_ci\' ENGINE=InnoDB DEFAULT CHARSET=utf8;',
        ];

        foreach ($sqls as $sql) {
            Shopware()->Db()->query($sql);
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
        /** @var $cacheManager \Shopware\Components\CacheManager */
        $cacheManager = $this->container->get('shopware.cache_manager');
        $cacheManager->clearConfigCache();
    }

    private function createExportDir() {
        if (!is_dir(ArticleExport::STORAGE)) {
            mkdir(ArticleExport::STORAGE, 0775, true);
        }
    }

    private function deleteExportDir() {
        $this->rrmdir(ArticleExport::STORAGE);
    }

    private function rrmdir($dir) {
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
