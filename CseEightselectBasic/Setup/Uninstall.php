<?php

namespace CseEightselectBasic\Setup;

use CseEightselectBasic\Services\Export\Connector;
use CseEightselectBasic\Services\Export\StatusExportDelta;
use CseEightselectBasic\Setup\Helpers\EmotionComponents;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use CseEightselectBasic\Setup\Helpers\MenuEntry;

class Uninstall implements SetupInterface
{

    /**
     * @var UninstallContext
     */
    private $context;

    /**
     * @var EmotionComponents
     */
    private $emotionComponents;

    /**
     * @var Connector
     */
    private $cseConnector;

    /**
     * @var StatusExportDelta
     */
    private $statusExportDelta;

    /**
     * @var MenuEntry
     */
    private $menuEntry;

    /**
     * @param UninstallContext $context
     * @param EmotionComponents $emotionComponents
     * @param StatusExportDelta $statusExportDelta
     * @param MenuEntry $menuEntry
     */
    public function __construct(
        UninstallContext $context,
        EmotionComponents $emotionComponents,
        StatusExportDelta $statusExportDelta,
        MenuEntry $menuEntry
    ) {
        $this->context = $context;
        $this->emotionComponents = $emotionComponents;
        $this->statusExportDelta = $statusExportDelta;
        $this->menuEntry = $menuEntry;
    }

    public function execute()
    {
        // @todo implement uninstall widgets ($this->emotionComponents->remove())
        $this->statusExportDelta->uninstall();
        $this->menuEntry->remove();
        $this->context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }
}
