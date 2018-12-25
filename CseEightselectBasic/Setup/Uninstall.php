<?php

namespace CseEightselectBasic\Setup;

use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;

class Uninstall implements SetupInterface
{

    /**
     * @var UninstallContext
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
     * @param UninstallContext $context
     * @param SizeAttribute $sizeAttribute
     * @param EmotionComponents $emotionComponents
     */
    public function __construct(
        UninstallContext $context,
        SizeAttribute $sizeAttribute,
        EmotionComponents $emotionComponents
    ) {
        $this->context = $context;
        $this->sizeAttribute = $sizeAttribute;
        $this->emotionComponents = $emotionComponents;
    }

    public function execute()
    {
        $this->sizeAttribute->remove();
        // @todo implement uninstall widgets
        $this->context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }
}
