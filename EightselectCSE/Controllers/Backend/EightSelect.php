<?php

use EightselectCSE\Models\EightSelectAttribute;

class Shopware_Controllers_Backend_EightSelect extends \Shopware_Controllers_Backend_Application
{
    protected $model = EightSelectAttribute::class;
    protected $alias = 'eightSelectAttribute';

    public function getArticleAttributesAction()
    {
        $this->View()->assign($this->getArticleAttributes());
    }

    /**
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     * @return array
     */
    protected function getArticleAttributes()
    {
        $fixedAttributes = [
            // articles attributes
            ['column_name' => '-', 'label' => '-'],
            ['column_name' => 's_articles.name', 'label' => 'Name'],
            ['column_name' => 's_articles.description', 'label' => 'Description'],
            ['column_name' => 's_articles.description_long', 'label' => 'Description long'],
            ['column_name' => 's_articles.keywords', 'label' => 'Keywords'],
            // details attributes
            ['column_name' => 's_articles_details.ordernumber', 'label' => 'Ordernumber'],
            ['column_name' => 'additionaltext', 'label' => 'Additional text'],
            ['column_name' => 'weight', 'label' => 'Weight'],
            ['column_name' => 'width', 'label' => 'Width'],
            ['column_name' => 'height', 'label' => 'Height'],
            ['column_name' => 'length', 'label' => 'Length'],
            ['column_name' => 'ean', 'label' => 'EAN'],
        ];

        $attributeData = Shopware()->Db()->query('SELECT `column_name`, label FROM s_attribute_configuration WHERE table_name = "s_articles_attributes"')->fetchAll();
        foreach ($attributeData as &$attributeDatum) {
            $attributeDatum['column_name'] = 's_articles_attributes.' . $attributeDatum['column_name'];
        }

        $attributesComplete = array_merge($fixedAttributes, $attributeData);
        return [
            'success' => true,
            'data'    => $attributesComplete,
        ];
    }
}
