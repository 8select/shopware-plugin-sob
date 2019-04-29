<?php

namespace CseEightselectBasic\Setup;

use CseEightselectBasic\Services\Export\Connector;
use CseEightselectBasic\Services\Export\StatusExportDelta;
use CseEightselectBasic\Setup\Helpers\EmotionComponents;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;

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
     * @param UninstallContext $context
     * @param EmotionComponents $emotionComponents
     * @param StatusExportDelta $statusExportDelta
     */
    public function __construct(
        UninstallContext $context,
        EmotionComponents $emotionComponents,
        StatusExportDelta $statusExportDelta
    ) {
        $this->context = $context;
        $this->emotionComponents = $emotionComponents;
        $this->statusExportDelta = $statusExportDelta;
    }

    public function execute()
    {
        // @todo implement uninstall widgets ($this->emotionComponents->remove())
        $this->statusExportDelta->uninstall();
        $this->context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }
}
