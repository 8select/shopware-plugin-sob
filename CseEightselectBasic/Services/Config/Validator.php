<?php

namespace CseEightselectBasic\Services\Config;

use CseEightselectBasic\Services\PluginConfig\PluginConfig as PluginConfigService;
use Doctrine\DBAL\Connection;

class Validator
{
    /**
     * @var PluginConfigService
     */
    private $pluginConfigService;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Provider $provider
     */
    public function __construct(PluginConfigService $pluginConfigService, Connection $connection)
    {
        $this->pluginConfigService = $pluginConfigService;
        $this->connection = $connection;
    }

    /**
     * @return array
     */
    public function validateWidgetConfig()
    {
        $violations = [];
        if ($this->pluginConfigService->isCseActiveForCurrentShop() === false) {
            array_push($violations, "Plugin ist nicht aktiv");
        }

        array_push($violations, $this->validateApiId());

        $violationsWithoutNull = array_filter($violations);

        return [
            'isValid' => empty($violationsWithoutNull),
            'violations' => array_values($violationsWithoutNull),
        ];
    }

    /**
     * @return array
     */
    public function validateExportConfig()
    {
        $violations = [];
        if ($this->pluginConfigService->isCseActive() === false) {
            array_push($violations, "Plugin ist nicht aktiv");
        }

        array_push($violations, $this->validateApiId());
        array_push($violations, $this->validateFeedId());

        $violationsWithoutNull = array_filter($violations);

        return [
            'isValid' => empty($violationsWithoutNull),
            'violations' => array_values($violationsWithoutNull),
        ];
    }

    /**
     * @return array
     */
    public function validateConfig()
    {
        $violations = [];
        if ($this->pluginConfigService->isCseActive() === false) {
            array_push($violations, "Plugin ist nicht aktiv");
        }

        array_push($violations, $this->validateApiId());
        array_push($violations, $this->validateFeedId());
        array_push($violations, $this->validateSysPsvContainer());
        array_push($violations, $this->validateSysAccContainer());

        $violationsWithoutNull = array_filter($violations);

        return [
            'isValid' => empty($violationsWithoutNull),
            'violations' => array_values($violationsWithoutNull),
        ];
    }

    private function validateApiId()
    {
        if (strlen($this->pluginConfigService->get('CseEightselectBasicApiId')) !== 36) {
            return "Die hinterlegte API ID ist ungültig";
        }
    }

    private function validateFeedId()
    {
        if (strlen($this->pluginConfigService->get('CseEightselectBasicFeedId')) !== 36) {
            return "Die hinterlegte Feed ID ist ungültig";
        }
    }

    private function validateSysPsvContainer()
    {
        $container = $this->pluginConfigService->get('CseEightselectBasicSysPsvContainer');
        if (strlen($container) === 0 || strpos($container, 'CSE_SYS') === false) {
            return "Kein Widget-Platzhalter (CSE_SYS) im SYS-PSV HTML-Container";
        }
    }

    private function validateSysAccContainer()
    {
        $container = $this->pluginConfigService->get('CseEightselectBasicSysAccContainer');
        if (strlen($container) === 0 || strpos($container, 'CSE_SYS') === false) {
            return "Kein Widget-Platzhalter (CSE_SYS) im SYS-ACC HTML-Container";
        }
    }
}
