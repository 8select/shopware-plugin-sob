<?php

namespace CseEightselectBasic\Setup;

use CseEightselectBasic\Services\Export\StatusExportDelta;
use CseEightselectBasic\Setup\Helpers\EmotionComponents;
use CseEightselectBasic\Setup\Helpers\SizeAttribute;
use Shopware\Components\Plugin\Context\InstallContext;

class Install implements SetupInterface
{

    /**
     * @var InstallContext
     */
    private $context;

    /**
     * @var SizeAttribute
     */
    private $sizeAttribute;

    /**
     * @var EmotionComponents
     */
    private $emotionComponents;

    /**
     * @var StatusExportDelta
     */
    private $statusExportDelta;

    /**
     * @param InstallContext $context
     * @param SizeAttribute $sizeAttribute
     * @param EmotionComponents $emotionComponents
     * @param StatusExportDelta $statusExportDelta
     */
    public function __construct(
        InstallContext $context,
        SizeAttribute $sizeAttribute,
        EmotionComponents $emotionComponents,
        StatusExportDelta $statusExportDelta
    ) {
        $this->context = $context;
        $this->sizeAttribute = $sizeAttribute;
        $this->emotionComponents = $emotionComponents;
        $this->statusExportDelta = $statusExportDelta;
    }

    public function execute()
    {
        $this->emotionComponents->create();
        $this->statusExportDelta->install();
        $this->context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }
}
