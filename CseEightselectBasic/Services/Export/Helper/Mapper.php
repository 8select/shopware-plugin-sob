<?php

namespace CseEightselectBasic\Services\Export\Helper;

class Mapper
{
    /**
     * @var ProductUrl
     */
    private $urlHelper;

    /**
     * @var ProductImages
     */
    private $imageHelper;

    /**
     * @var \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * @param ProductUrl                               $urlHelper
     * @param ProductImages                            $imageHelper
     * @param \Enlight_Components_Db_Adapter_Pdo_Mysql $db
     */
    public function __construct($urlHelper, $imageHelper, $db)
    {
        $this->urlHelper = $urlHelper;
        $this->imageHelper = $imageHelper;
        $this->db = $db;
    }

    /**
     * @param array $article
     * @param array $fields
     *
     * @return array
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public function map($article, $fields)
    {
        $line = [];

        /** @var array $categories */
        $categories = $this->getCategories($article['articleID']);

        foreach ($fields as $mappedFieldName => $fieldName) {
            switch ($fieldName) {
                case 'mastersku':
                    $value = $article['mastersku'];
                    $options = $this->getNonSizeConfiguratorOptionsByArticleDetailId($article['detailID']);
                    if (!empty($options)) {
                        $value .= '-' . mb_strtolower(str_replace(' ', '-', implode('-', $options)));
                    }
                    break;
                case 'model':
                    $value = $article['mastersku'];
                    break;
                case 'kategorie1':
                    $value = !empty($categories[0]) ? $categories[0] : '';
                    break;
                case 'kategorie2':
                    $value = !empty($categories[1]) ? $categories[1] : '';
                    break;
                case 'kategorie3':
                    $value = !empty($categories[2]) ? $categories[2] : '';
                    break;
                case 'streich_preis':
                case 'angebots_preis':
                    $value = ProductPrice::getGrossPrice($article, $fieldName);
                    break;
                case 'produkt_url':
                    $value = $this->urlHelper->getUrl($article['articleID'], $article['sku'], $article['name1']);
                    break;
                case 'bilder':
                    $value = $this->imageHelper->getImageUrls($article['articleID'], $article['sku']);
                    break;
                case 'status':
                    $value = $this->getStatus($article['active'], $article['instock'], $article['laststock']);
                    break;
                case 'groesse':
                    $size = $this->getSizeOptionByArticleDetailId($article['detailID']);
                    $value = !empty($size) ? $size : 'onesize';
                    break;
                case 'beschreibung1':
                    $withHtml = $this->getValue($article, 'beschreibung');
                    $withExtraSpaces = str_replace('>', '> ', $withHtml);
                    $withOutHtml = strip_tags($withExtraSpaces);
                    $value = html_entity_decode($withOutHtml);
                    break;
                default:
                    $value = $this->getValue($article, $fieldName);
            }

            $line[$mappedFieldName] = $value;
        }

        return $line;
    }

    /**
     * @param $article
     * @param $field
     *
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     *
     * @return string
     */
    private function getValue($article, $field)
    {
        $value = $article[$field];
        if ($value) {
            return $value;
        }

        $attributes = array_filter(
            explode(',', $this->getGroupOrFilterAttribute($field))
        );

        if ($attributes) {
            $values = $this->filterRelevantAttributeValues($attributes, $article);

            return implode('|', array_filter($values));
        }

        return '';
    }

    /**
     * @param array $attributes
     * @param array $article
     *
     * @return array
     */
    private function filterRelevantAttributeValues($attributes, $article)
    {
        $groups = array_filter($attributes, function ($attr) {
            return strpos($attr, 'group') !== false;
        });

        if ($groups) {
            $groupIds = array_map(function ($group) {
                return explode('id=', $group)[1];
            }, $groups);

            $groupValues = array_filter(
                array_map(function ($id) use ($article) {
                    return $this->getConfiguratorGroupValue($article['detailID'], $id);
                }, $groupIds)
            );

            if ($groupValues) {
                return $groupValues;
            }
        }

        $filters = array_filter($attributes, function ($attr) {
            return strpos($attr, 'filter');
        });

        if ($filters) {
            $filterIds = array_filter(array_map(function ($filter) {
                return explode('id=', $filter)[1];
            }, $filters));

            $filterValues = array_filter(
                array_map(function ($id) use ($article) {
                    return $this->getFilterValues($article['articleID'], $id);
                }, $filterIds)
            );

            if ($filterValues) {
                return $filterValues;
            }
        }

        return [];
    }

    /**
     * @param $articleId
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     *
     * @return array
     */
    private function getCategories($articleId)
    {
        $categoryIDs = $this->db->query('SELECT categoryID FROM s_articles_categories WHERE articleID = ?', [$articleId])->fetchAll();
        $categoriesList = [];
        foreach ($categoryIDs as $categorieID) {
            $categoryPathResults = $this->getCategoriesByParent((int) $categorieID['categoryID']);

            $categoryNames = [];
            foreach ($categoryPathResults as $categoryPathResult) {
                $categoryNames[] = $categoryPathResult['name'];
            }

            $categoriesList[] = implode(' ', $categoryNames);
        }

        return $categoriesList;
    }

    /**
     * @param $categoryId
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return array
     */
    private function getCategoriesByParent($categoryId)
    {
        $pathCategories = Shopware()->Models()->getRepository('Shopware\Models\Category\Category')->getPathById($categoryId, [
            'id',
            'name',
            'parentId',
        ]);
        $categories = [];

        foreach ($pathCategories as $category) {
            if ($category['parentId'] == 1) {
                continue;
            }

            $categories[] = $category;
        }

        return $categories;
    }

    private function getStatus($active, $instock, $laststock)
    {
        if ($active && (!$laststock || $instock > 0)) {
            return '1';
        }

        return '0';
    }

    /**
     * @param $field
     *
     * @return string
     *
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    private function getGroupOrFilterAttribute($field)
    {
        $query = 'SELECT shopwareAttribute as groupId
                      FROM 8s_attribute_mapping
                      WHERE (shopwareAttribute LIKE "%group%" OR shopwareAttribute LIKE "%filter%")
                      AND eightselectAttribute = "' . $field . '"';

        return $this->db->query($query)->fetchColumn();
    }

    /**
     * @param $articleId
     * @param $groupId
     *
     * @return string
     *
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    private function getConfiguratorGroupValue($detailId, $groupId)
    {
        $sql = 'SELECT s_article_configurator_options.name as name
                FROM s_article_configurator_options
                INNER JOIN s_article_configurator_option_relations on s_article_configurator_option_relations.option_id = s_article_configurator_options.id
                WHERE s_article_configurator_option_relations.article_id = ' . $detailId . '
                AND s_article_configurator_options.group_id = ' . $groupId;

        return $this->db->query($sql)->fetchColumn();
    }

    /**
     * @param int $articleId
     *
     * @return array
     *
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    private function getNonSizeConfiguratorOptionsByArticleDetailId($articleId)
    {
        /*
         * Query Explanation:
         *
         * (SELECT) Get all option names
         *  (JOIN) where the options belongs to the article
         *  (JOIN) AND the option belongs to a group
         *  (JOIN) which has an attribute
         * (WHERE) which is not defined as the basic size
         * (AND) and the article has a given ID
         */
        $sql = <<<SQL
SELECT s_article_configurator_options.name as value
FROM s_article_configurator_options
	INNER JOIN s_article_configurator_option_relations ON s_article_configurator_options.id = s_article_configurator_option_relations.option_id
	INNER JOIN s_article_configurator_groups ON s_article_configurator_groups.id = s_article_configurator_options.group_id
	LEFT JOIN s_article_configurator_groups_attributes on s_article_configurator_groups.id = s_article_configurator_groups_attributes.groupID
WHERE (s_article_configurator_groups_attributes.od_cse_eightselect_basic_is_size = 0 OR s_article_configurator_groups_attributes.od_cse_eightselect_basic_is_size IS NULL)
AND s_article_configurator_option_relations.article_id = $articleId
SQL;

        return array_column($this->db->query($sql)->fetchAll(\Zend_Db::FETCH_ASSOC), 'value');
    }

    /**
     * @param int $articleId
     *
     * @return string
     *
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    private function getSizeOptionByArticleDetailId($articleId)
    {
        $sql = <<<SQL
SELECT s_article_configurator_options.name as value
FROM s_article_configurator_options
	INNER JOIN s_article_configurator_option_relations ON s_article_configurator_options.id = s_article_configurator_option_relations.option_id
	INNER JOIN s_article_configurator_groups ON s_article_configurator_groups.id = s_article_configurator_options.group_id
	INNER JOIN s_article_configurator_groups_attributes on s_article_configurator_groups.id = s_article_configurator_groups_attributes.groupID
WHERE s_article_configurator_option_relations.article_id = $articleId
AND (s_article_configurator_groups_attributes.od_cse_eightselect_basic_is_size = 1);
SQL;

        return $this->db->query($sql)->fetchColumn();
    }

    /**
     * @param $articleId
     * @param $filterId
     *
     * @return string
     *
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    private function getFilterValues($articleId, $filterId)
    {
        if (!$articleId || !$filterId) {
            return '';
        }
        $sql = 'SELECT s_filter_values.value as name
                FROM s_filter_values
                INNER JOIN s_filter_articles on s_filter_articles.valueID = s_filter_values.id
                WHERE s_filter_articles.articleID = ' . $articleId . '
                AND s_filter_values.optionID = ' . $filterId;
        $value = $this->db->query($sql)->fetchAll();

        return implode('|', array_column($value, 'name'));
    }
}
