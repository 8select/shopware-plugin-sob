<?php

namespace CseEightselectBasic\Services\Config;

use CseEightselectBasic\Services\Dependencies\Provider;

class Validator
{
    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var array
     */
    private $pluginConfig;

    /**
     * @param Provider $provider
     */
    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
        $this->pluginConfig = $provider->getPluginConfig();
    }

    /**
     * @return bool
     */
    public function isPluginActive()
    {
        return $this->pluginConfig['CseEightselectBasicPluginActive'];
    }

    /**
     * @return array
     */
    public function validateConfig()
    {
        $violations = [];

        // braucht nicht aktiv sein - es kann sein, dass das plugin im hauptshop nicht aktiv ist, dafür aber in einem subshop - im backend muss bei diversen aktionen immer der shop gesucht werden bei dem das plugin aktiv ist
        // if ($this->isPluginActive() === false) {
        //     array_push($violations, "Plugin ist nicht aktiv");
        // }

        if (strlen($this->pluginConfig['CseEightselectBasicApiId']) !== 36) {
            array_push($violations, "Die hinterlegte API ID ist ungültig");
        }

        if (strlen($this->pluginConfig['CseEightselectBasicFeedId']) !== 36) {
            array_push($violations, "Die hinterlegte Feed ID ist ungültig");
        }

        $sysPsvContainer = $this->pluginConfig['CseEightselectBasicSysPsvContainer'];
        if ( strlen($sysPsvContainer) === 0 || strpos($sysPsvContainer, 'CSE_SYS') === false ) {
            array_push($violations, "Kein Widget-Platzhalter (CSE_SYS) im SYS-PSV HTML-Container");
        }

        $dbConnection = $this->provider->getDbConnection();
        $sql = 'SELECT SUM(od_cse_eightselect_basic_is_size = 1) FROM s_article_configurator_groups_attributes';
        $hasMappedSizeAttribute = (bool)$connection->fetchOne($sql);

        if ($hasMappedSizeAttribute === false) {
            $noSizesMessage = "Größenrelevante Attributegruppen wurden nicht definiert. Mehr Infos finden Sie in der " .
            "<a href='https://www.8select.com/8select-cse-installationsanleitung-shopware#5-konfiguration-attributfelder' target='_blank'>Installationsanleitung</a>";
            array_push($violations, $noSizesMessage);
        }

        return [
            'isValid' => empty($violations),
            'violations' => $violations
        ];
    }
}
