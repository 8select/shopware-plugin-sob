<?php

namespace CseEightselectBasic\Setup\Helpers;

use Doctrine\DBAL\Connection;
use Shopware\Models\Menu\Menu;
use Shopware\Models\Menu\Repository as MenuRepository;
use Shopware\Models\Plugin\Plugin;

class MenuEntry
{

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var int
     */
    private $pluginId;

    /**
     * @var Plugin
     */
    private $plugin;

    /**
     * @param Connection $connection
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
    private function getPlugin()
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
     * @return MenuRepository
     */
    private function getParentMenu()
    {
        $repository = Shopware()->Models()->getRepository(Menu::class);

        return $repository->findOneBy(['controller' => 'ConfigurationMenu']);
    }

    /**
     * @return Menu|null
     */
    private function createMenuItem(array $options)
    {
        if (!isset($options['label'])) {
            return null;
        }
        $item = new Menu();
        $item->fromArray($options);
        $plugin = $this->getPlugin();
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
                    'parent' => $this->getParentMenu(),
                    'class' => 'eightselect--icon',
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
