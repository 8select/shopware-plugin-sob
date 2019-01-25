<?php

namespace CseEightselectBasic\Setup;

use CseEightselectBasic\Services\Export\Connector;
use CseEightselectBasic\Setup\Helpers\EmotionComponents;
use CseEightselectBasic\Setup\Helpers\SizeAttribute;
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
     * @var Connector
     */
    private $cseConnector;

    /**
     * @param UninstallContext $context
     * @param SizeAttribute $sizeAttribute
     * @param EmotionComponents $emotionComponents
     * @param Connector $cseConnector
     */
    public function __construct(
        UninstallContext $context,
        SizeAttribute $sizeAttribute,
        EmotionComponents $emotionComponents,
        Connector $cseConnector
    ) {
        $this->context = $context;
        $this->sizeAttribute = $sizeAttribute;
        $this->emotionComponents = $emotionComponents;
        $this->cseConnector = $cseConnector;
    }

    public function execute()
    {
        $this->cseConnector->disconnect();
        $this->sizeAttribute->remove();
        // @todo implement uninstall widgets ($this->emotionComponents->remove())
        $this->context->scheduleClearCache(InstallContext::CACHE_LIST_ALL);
    }
}
