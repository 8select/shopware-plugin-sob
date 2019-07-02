<?php

namespace CseEightselectBasic\Setup\Helpers;

use Shopware\Models\Menu\Menu;
use Shopware\Models\Menu\Repository as MenuRepository;
use Shopware\Models\Plugin\Plugin;
use Doctrine\DBAL\Connection;

class MenuEntry
{

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var int
     */
    private $pluginId; // CseEightselectBasic Plugin

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection, $pluginId)
    {
        $this->connection = $connection;
        $this->pluginId = $pluginId;
    }

    /**
     * @return Plugin
     */
    private function Plugin()
    {
        if ($this->plugin === null) {
            /** @var Plugin $plugin */
            $plugin = Shopware()->Models()->getRepository(Plugin::class)
                ->findOneBy(['id' => $this->pluginId]);
            $this->plugin = $plugin;
        }
        return $this->plugin;
    }

    /**
     * Returns shopware menu
     *
     * @return MenuRepository
     */
    private function Menu()
    {
        return Shopware()->Models()->getRepository(Menu::class);
    }

    /**
     * Create a new menu item instance
     *
     * @return Menu|null
     */
    private function createMenuItem(array $options)
    {
        if (!isset($options['label'])) {
            return null;
        }
        $item = new Menu();
        $item->fromArray($options);
        $plugin = $this->Plugin();
        $plugin->getMenuItems()->add($item);
        $item->setPlugin($plugin);
        return $item;
    }

    public function create()
    {
        try {
            $this->createMenuItem(
                array(
                    'label' => '8select',
                    'controller' => 'CseEightselectBasic',
                    'action' => 'Index',
                    'onclick' => 'window.open("https://console.8select.io");',
                    'active' => 1,
                    'parent' => $this->Menu()->findOneBy(['controller' => 'ConfigurationMenu']),
                    'class' => 'eightselect--icon'
                )
            );
        } catch (\Exception $exception) {
            $template = 'could not add menu entry `8select` due to exception: %s';
            $message = sprintf($template, $exception->getMessage());
            $context = [
                'exception' => [
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTrace(),
                ],
            ];
            Shopware()->Container()->get('pluginlogger')->warning($message, $context);
        }
    }

    public function remove()
    {
        $this->connection->executeUpdate(
            'DELETE FROM s_core_menu WHERE `pluginID` = ?',
            [$this->pluginId]
        );
    }
}
