<?php

namespace CseEightselectBasic\Setup;

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
     * @param InstallContext $context
     * @param SizeAttribute $sizeAttribute
     * @param EmotionComponents $emotionComponents
     */
    public function __construct(
        InstallContext $context,
        SizeAttribute $sizeAttribute,
        EmotionComponents $emotionComponents
    ) {
        $this->context = $context;
        $this->sizeAttribute = $sizeAttribute;
        $this->emotionComponents = $emotionComponents;
    }

    public function execute()
    {
        $this->emotionComponents->create();
        $this->sizeAttribute->create();
        $this->context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }
}
