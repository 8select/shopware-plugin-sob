<?php

namespace CseEightselectBasic\Setup;

use CseEightselectBasic\Services\Export\StatusExportDelta;
use CseEightselectBasic\Setup\Helpers\EmotionComponents;
use CseEightselectBasic\Setup\Helpers\MenuEntry;
use Shopware\Components\Plugin\Context\InstallContext;

class Install implements SetupInterface
{

    /**
     * @var InstallContext
     */
    private $context;

    /**
     * @var EmotionComponents
     */
    private $emotionComponents;

    /**
     * @var StatusExportDelta
     */
    private $statusExportDelta;

    /**
     * @var MenuEntry
     */
    private $menuEntry;

    /**
     * @param InstallContext $context
     * @param EmotionComponents $emotionComponents
     * @param StatusExportDelta $statusExportDelta
     */
    public function __construct(
        InstallContext $context,
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
        $this->emotionComponents->create();
        $this->statusExportDelta->install();
        $this->menuEntry->create();
        $this->context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }
}
