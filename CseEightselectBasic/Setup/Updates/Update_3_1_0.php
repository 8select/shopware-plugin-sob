<?php

namespace CseEightselectBasic\Setup\Updates;

use CseEightselectBasic\Setup\Helpers\MenuEntry;
use CseEightselectBasic\Setup\SetupInterface;
use Doctrine\DBAL\Connection;

class Update_3_1_0 implements SetupInterface
{
    /**
     * @var MenuEntry
     */
    private $menuEntry;

    /**
     * @param MenuEntry $menuEntry
     */
    public function __construct(MenuEntry $menuEntry)
    {
        $this->menuEntry = $menuEntry;
    }

    public function execute()
    {
        $this->createEightselectMenuEntry();
    }


    private function createEightselectMenuEntry()
    {
        $this->menuEntry->remove();
        $this->menuEntry->create();
    }
}
