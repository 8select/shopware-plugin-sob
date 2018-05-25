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
        $this->removeExportOnceCron();
        $this->removeUpdateCron();
        $this->removeDatabase();
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
                'eightselectAttributeLabel'      => 'EAN-CODE',
                'eightselectAttributeLabelDescr' => 'Standardtisierte eindeutige Materialnummer nach EAN\n(European Article Number) oder UPC (Unified Product Code).',
                'eightselectAttributeGroupName'  => 'Gruppe A',
                'shopwareAttribute'              => 's_articles_details.ean',

            ],
            [
                'eightselectAttribute'           => 'model',
                'eightselectAttributeLabel'      => 'PRODUKT-MODELL ID',
                'eightselectAttributeLabelDescr' => 'Ein Modell bezeichnet das "Grundprodukt". ID kann Name\noder Nummer sein. Ein Modell kann in mehreren Ausführungen vorkommen, die sich z.B. in Größe, Farbe oder Muster unterschieden. Beispiel: Das Modell "Arie" (1234) gibt es in den 3 Farben: "blau", "rot", "gelb" (567) und in den 4 Größen: "S", "M", "L", "XL" (89), also 12 Ausführungen. Jede Ausführung hat eine eindeutige Artikelnummer.',
                'eightselectAttributeGroupName'  => 'Gruppe A',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'name1',
                'eightselectAttributeLabel'      => 'ARTIKELBEZEICHNUNG',
                'eightselectAttributeLabelDescr' => 'Standardbezeichnung für den Artikel so wie er normal-\nerweise in der Artikeldetailansicht genutzt wird (z.B. Sportliches Herren-Hemd "Arie")',
                'eightselectAttributeGroupName'  => 'Gruppe A',
                'shopwareAttribute'              => 's_articles.name',
            ],
            [
                'eightselectAttribute'           => 'name2',
                'eightselectAttributeLabel'      => 'ALTERNATIVE ARTIKELBEZEICHNUNG',
                'eightselectAttributeLabelDescr' => 'Oft als Kurzbezeichnung in Listenansichten verwendet\n(z.B. "Freizeit-Hemd") oder für Google mit mehr Infos zur besseren Suche',
                'eightselectAttributeGroupName'  => 'Gruppe A',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'beschreibung',
                'eightselectAttributeLabel'      => 'BESCHREIBUNGSTEXT HTML',
                'eightselectAttributeLabelDescr' => 'Der Beschreibungstext zum Artikel, auch "description long"\ngenannt, im HTML-Format z.B. "<p>Federleichte Regenhose! </ br> ...</p>"',
                'eightselectAttributeGroupName'  => 'Gruppe A',
                'shopwareAttribute'              => 's_articles.description_long',
            ],
            [
                'eightselectAttribute'           => 'beschreibung1',
                'eightselectAttributeLabel'      => 'BESCHREIBUNGSTEXT',
                'eightselectAttributeLabelDescr' => 'Der Beschreibunstext zum Artikel, auch "description long"\ngenannt, in unformatierter Reintext-Form z.B. "Federleichte Regenhose! ..."',
                'eightselectAttributeGroupName'  => 'Gruppe A',
                'shopwareAttribute'              => 's_articles.description_long',
            ],
            [
                'eightselectAttribute'           => 'rubrik',
                'eightselectAttributeLabel'      => 'PRODUKTKATEGORIE',
                'eightselectAttributeLabelDescr' => 'Bezeichnung der Artikelgruppen, die meist so in der Shop-\nnavigation verwendet werden (z.B. Hosen, Jacken. Accessoires, Schuhe)',
                'eightselectAttributeGroupName'  => 'Gruppe A',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'typ',
                'eightselectAttributeLabel'      => 'PRODUKTTYP / UNTERKATEGORIE',
                'eightselectAttributeLabelDescr' => 'Verfeinerung der Ebene PRODUKTKATEGORIE \n(z.B. PRODUKTKATEGORIE = Jacken; PRODUKTTYP = Lederjacken, Parkas, Blousons)',
                'eightselectAttributeGroupName'  => 'Gruppe A',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'abteilung',
                'eightselectAttributeLabel'      => 'ABTEILUNG',
                'eightselectAttributeLabelDescr' => 'Einteilung der Sortimente nach Zielgruppen \n(z.B. Damen, Herren, Kinder)',
                'eightselectAttributeGroupName'  => 'Gruppe A',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'kiko',
                'eightselectAttributeLabel'      => 'KIKO',
                'eightselectAttributeLabelDescr' => 'Speziell für Kindersortimente: Einteilung nach \nZielgruppen (z.B. Mädchen, Jungen, Baby)',
                'eightselectAttributeGroupName'  => 'Gruppe A',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'bereich',
                'eightselectAttributeLabel'      => 'BEREICH',
                'eightselectAttributeLabelDescr' => 'Damit können Teilsortimente bezeichnet sein \n(z.B. Outdoor; Kosmetik; Trachten; Lifestyle)',
                'eightselectAttributeGroupName'  => 'Gruppe A',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'sportart',
                'eightselectAttributeLabel'      => 'SPORTART',
                'eightselectAttributeLabelDescr' => 'speziell bei Sportartikeln (z.B. Handball, Bike, Bergsteigen)',
                'eightselectAttributeGroupName'  => 'Gruppe A',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'serie',
                'eightselectAttributeLabel'      => 'SERIE',
                'eightselectAttributeLabelDescr' => 'Hier können Bezeichnungen für Serien übergeben werden, \num Artikelfamilien oder Sondereditionen zu kennzeichnen (z.B. Expert Line, Mountain Professional)',
                'eightselectAttributeGroupName'  => 'Gruppe A',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'gruppe',
                'eightselectAttributeLabel'      => 'GRUPPE / BAUKAUSTEN',
                'eightselectAttributeLabelDescr' => 'bezeichnet direkt zusammengehörige Artikel  (z.B. Bikini-Oberteil \n"Aloha"und Bikini-Unterteil  "Aloha"=  Gruppe 1002918; Baukasten-Sakko "Ernie" und Baukasten-Hose "Bert" = Gruppe "E&B"). Dabei können auch mehr als 2 Artikel eine Gruppe bilden (z.B. Mix & Match: Gruppe "Hawaii"  = 3 Bikini-Oberteile können mit 2 Bikini-Unterteilen frei kombiniert werden) . Die ID für eine Gruppe kann eine Nummer oder ein Name sein.',
                'eightselectAttributeGroupName'  => 'Gruppe A',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'saison',
                'eightselectAttributeLabel'      => 'SAISON',
                'eightselectAttributeLabelDescr' => 'Beschreibt zu welcher Saison bzw. saisonalen Kollektion der \nArtikel gehört (z.B. HW18/19; Sommer 2018; Winter)',
                'eightselectAttributeGroupName'  => 'Gruppe A',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'groesse',
                'eightselectAttributeLabel'      => 'GROESSE',
                'eightselectAttributeLabelDescr' => 'Ein Artikel kann verschiedene Größe haben. Alle Größensysteme \nsind zulässig. Artikel ohne Größe sind oft als "onesize" gekennzeichnet.',
                'eightselectAttributeGroupName'  => 'Gruppe B',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'farbe',
                'eightselectAttributeLabel'      => 'FARBE',
                'eightselectAttributeLabelDescr' => 'Die exakte Farbbezeichnung des Artikels \n(z.B. Gelb; Himbeerrot; Rosenrot)',
                'eightselectAttributeGroupName'  => 'Gruppe B',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'farbspektrum',
                'eightselectAttributeLabel'      => 'FARBSPEKTRUM',
                'eightselectAttributeLabelDescr' => 'Farben sind einem Farbspektrum zugeordnet \n(z.B. Farbe: Himbeerrot > Farbspektrum: Rot)',
                'eightselectAttributeGroupName'  => 'Gruppe B',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'muster',
                'eightselectAttributeLabel'      => 'MUSTER',
                'eightselectAttributeLabelDescr' => 'Farbmuster des Artikels (z.B. uni, einfarbig, \nkariert, gestreift, Blumenmuster, einfarbig-strukturiert)',
                'eightselectAttributeGroupName'  => 'Gruppe B',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'waschung',
                'eightselectAttributeLabel'      => 'WASCHUNG',
                'eightselectAttributeLabelDescr' => 'optische Wirkung des Materials (bei Jeans z.B. \nused, destroyed, bleached, vintage)',
                'eightselectAttributeGroupName'  => 'Gruppe B',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'stil',
                'eightselectAttributeLabel'      => 'STIL',
                'eightselectAttributeLabelDescr' => 'Stilrichtung des Artikels (z.B.  Business, Casual, \nEthno, Retro)',
                'eightselectAttributeGroupName'  => 'Gruppe B',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'detail',
                'eightselectAttributeLabel'      => 'DETAIL',
                'eightselectAttributeLabelDescr' => 'erwähnenswerte Details an Artikeln \n(z.B. Reißverschluss seitlich am Saum, Brusttasche, Volants, Netzeinsatz, Kragen in Kontrastfarbe)',
                'eightselectAttributeGroupName'  => 'Gruppe B',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'passform',
                'eightselectAttributeLabel'      => 'PASSFORM',
                'eightselectAttributeLabelDescr' => 'in Bezug auf die Körperform, wird häufig für \nHemden, Sakkos und Anzüge verwendet (z.B. schmal, bequeme Weite, slim-fit, regular-fit, comfort-fit, körpernah)',
                'eightselectAttributeGroupName'  => 'Gruppe C',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'schnitt',
                'eightselectAttributeLabel'      => 'SCHNITT',
                'eightselectAttributeLabelDescr' => 'in Bezug auf die Form des Artikels \n(z.B. Bootcut, gerades Bein, Oversized, spitzer Schuh)',
                'eightselectAttributeGroupName'  => 'Gruppe C',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'aermellaenge',
                'eightselectAttributeLabel'      => 'ÄRMELLÄNGE',
                'eightselectAttributeLabelDescr' => 'speziell bei Oberbekleidung: Länge der Ärmel \n(z.B. normal, extra-lange Ärmel, ärmellos, 3/4 Arm)',
                'eightselectAttributeGroupName'  => 'Gruppe C',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'kragenform',
                'eightselectAttributeLabel'      => 'KRAGENFORM',
                'eightselectAttributeLabelDescr' => 'speziell bei Oberbekleidung: Beschreibung des Kragens \noder Ausschnitts (z.B. Rollkragen, V-Ausschnitt, Blusenkragen, Haifischkragen)',
                'eightselectAttributeGroupName'  => 'Gruppe C',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'verschluss',
                'eightselectAttributeLabel'      => 'VERSCHLUSS',
                'eightselectAttributeLabelDescr' => 'beschreibt Verschlussarten (z.B: geknöpft, Reißverschluss, \nDruckknöpfe, Klettverschluss; Haken&Öse)',
                'eightselectAttributeGroupName'  => 'Gruppe C',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'obermaterial',
                'eightselectAttributeLabel'      => 'ART OBERMATERIAL',
                'eightselectAttributeLabelDescr' => 'wesentliches Material des Artikels (z.B. Wildleder, Denim, \nEdelstahl, Gewebe, Strick, Jersey, Sweat, Crash)',
                'eightselectAttributeGroupName'  => 'Gruppe D',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'material',
                'eightselectAttributeLabel'      => 'MATERIAL',
                'eightselectAttributeLabelDescr' => 'bezeichnet die genaue Materialzusammensetzung \n(z.B. 98% Baumwolle, 2% Elasthan)',
                'eightselectAttributeGroupName'  => 'Gruppe D',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'funktion',
                'eightselectAttributeLabel'      => 'FUNKTION',
                'eightselectAttributeLabelDescr' => 'beschreibt Materialfunktionen und -eigenschaften \n(z.b. schnelltrocknend, atmungsaktiv, 100% UV-Schutz;  pflegeleicht, bügelleicht, körperformend)',
                'eightselectAttributeGroupName'  => 'Gruppe D',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'eigenschaft',
                'eightselectAttributeLabel'      => 'EIGENSCHAFT / EINSATZBEREICH',
                'eightselectAttributeLabelDescr' => 'speziell für Sport und Outdoor. Hinweise zum Einsatzbereich \n(Bsp. Schlafsack geeignet für Temparaturbereich 1 °C bis -16 °C, kratzfest, wasserdicht)',
                'eightselectAttributeGroupName'  => 'Gruppe D',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'auspraegung',
                'eightselectAttributeLabel'      => 'AUSFÜHRUNG & MAßANGABEN',
                'eightselectAttributeLabelDescr' => 'speziell für Sport und Outdoor. Wichtige Informationen, \ndie helfen, den Artikel in das Sortiment einzuordnen (Beispiele: bei Rucksäcken: Volumen "30-55 Liter"; bei Skistöcken: Größenangaben in Maßeinheit "Körpergröße 160 bis 175cm";  Sonderausführungen: "Linkshänder")',
                'eightselectAttributeGroupName'  => 'Gruppe E',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'fuellmenge',
                'eightselectAttributeLabel'      => 'FUELLMENGE',
                'eightselectAttributeLabelDescr' => 'bezieht sich auf die Menge des Inhalts des Artikels \n(z.B. 200ml; 0,5 Liter, 3kg, 150 Stück)',
                'eightselectAttributeGroupName'  => 'Gruppe E',
                'shopwareAttribute'              => '-',
            ],
            [
                'eightselectAttribute'           => 'absatzhoehe',
                'eightselectAttributeLabel'      => 'ABSATZHÖHE',
                'eightselectAttributeLabelDescr' => 'speziell bei Schuhen: Höhe des Absatzes (Format mit \noder ohne Maßeinheit z.B. 5,5 cm oder 5,5)',
                'eightselectAttributeGroupName'  => 'Gruppe F',
                'shopwareAttribute'              => '-',
            ]
        ];

        foreach ($attributeList as $attributeEntry) {
            $sql = 'INSERT INTO 8s_attribute_mapping (eightselectAttribute, eightselectAttributeLabel, eightselectAttributeLabelDescr, eightselectAttributeGroupName, shopwareAttribute) VALUES (?, ?, ?, ?, ?)';
            Shopware()->Db()->query(
                $sql,
                [
                    $attributeEntry['eightselectAttribute'],
                    $attributeEntry['eightselectAttributeLabel'],
                    $attributeEntry['eightselectAttributeLabelDescr'],
                    $attributeEntry['eightselectAttributeGroupName'],
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
}
