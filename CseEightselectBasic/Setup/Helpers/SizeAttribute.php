<?php

namespace CseEightselectBasic\Setup\Helpers;

use Doctrine\Common\Cache\ClearableCache;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;

class SizeAttribute
{

    /**
     * @var CrudService
     */
    private $attributeService;

    /**
     * @var ClearableCache
     */
    private $metaDataCache;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param CrudService $attributeService
     * @param ClearableCache $metaDataCache
     * @param ModelManager $modelManager
     */
    public function __construct(
        CrudService $attributeService,
        ClearableCache $metaDataCache,
        ModelManager $modelManager
    ) {
        $this->attributeService = $attributeService;
        $this->metaDataCache = $metaDataCache;
        $this->modelManager = $modelManager;
    }

    public function remove()
    {
        $this->attributeService->delete(
            's_article_configurator_groups_attributes',
            'od_cse_eightselect_basic_is_size',
            true
        );
        $this->metaDataCache->deleteAll();
        $this->modelManager->generateAttributeModels(['s_article_configurator_groups_attributes']);
    }
}
