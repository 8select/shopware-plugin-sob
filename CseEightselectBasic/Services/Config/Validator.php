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
    public function validateExportConfig()
    {
        var_dump($this->pluginConfig);
        $violations = [];
        array_push($violations, $this->validateApiId());
        array_push($violations, $this->validateFeedId());
        array_push($violations, $this->validateMappedSizeAttribute());

        $violationsWithoutNull = array_filter($violations);

        return [
            'isValid' => empty($violationsWithoutNull),
            'violations' => $violationsWithoutNull
        ];
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

        array_push($violations, $this->validateApiId());
        array_push($violations, $this->validateFeedId());
        array_push($violations, $this->validateSysPsvContainer());
        array_push($violations, $this->validateMappedSizeAttribute());

        $violationsWithoutNull = array_filter($violations);

        return [
            'isValid' => empty($violationsWithoutNull),
            'violations' => $violationsWithoutNull
        ];
    }

    private function validateApiId()
    {
        if (strlen($this->pluginConfig['CseEightselectBasicApiId']) !== 36) {
            return "Die hinterlegte API ID ist ungültig";
        }
    }

    private function validateFeedId()
    {
        if (strlen($this->pluginConfig['CseEightselectBasicFeedId']) !== 36) {
            return "Die hinterlegte Feed ID ist ungültig";
        }
    }

    private function validateSysPsvContainer()
    {
        $sysPsvContainer = $this->pluginConfig['CseEightselectBasicSysPsvContainer'];
        if ( strlen($sysPsvContainer) === 0 || strpos($sysPsvContainer, 'CSE_SYS') === false ) {
            return "Kein Widget-Platzhalter (CSE_SYS) im SYS-PSV HTML-Container";
        }
    }

    private function validateMappedSizeAttribute()
    {
        $dbConnection = $this->provider->getDbConnection();
        $sql = "SELECT count(*) FROM s_article_configurator_groups_attributes WHERE od_cse_eightselect_basic_is_size = 1;";
        $result = $dbConnection->fetchColumn($sql);
        dump($result);
        $hasMappedSizeAttribute = (bool) $result;

        if ($hasMappedSizeAttribute === false) {
            return "Größenrelevante Attributegruppen wurden nicht definiert. Mehr Infos finden Sie in der " .
            "<a href='https://www.8select.com/8select-cse-installationsanleitung-shopware#5-konfiguration-attributfelder' target='_blank'>Installationsanleitung</a>";
        }
    }
}
