<?php
namespace CseEightselectBasic\Components;

use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Models\Shop\Shop;

class FieldHelper
{
    /**
     * @param $article
     * @param $fields
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public static function getLine($article, $fields)
    {
        $line = [];

        /** @var array $categories */
        $categories = self::getCategories($article['articleID']);

        $sql = 'SELECT ordernumber, id FROM s_articles_details WHERE articleID = ? AND kind = 1';
        $articleDetail = Shopware()->Db()->query($sql, [$article['articleID']])->fetch();

        foreach ($fields as $field) {
            switch ($field) {
                case 'mastersku':
                    $value = $articleDetail['ordernumber'];
                    $options = static::getNonSizeConfiguratorOptionsByArticleDetailId($article['detailID']);
                    if (!empty($options)) {
                        $value .= '-' . mb_strtolower(str_replace(' ', '-', implode('-', $options)));
                    }
                    break;
                case 'model':
                    $value = $articleDetail['ordernumber'];
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
                    $value = PriceHelper::getGrossPrice($article, $field);
                    break;
                case 'produkt_url':
                    $value = self::getUrl($article['articleID']);
                    break;
                case 'bilder':
                    $value = self::getImages($article['articleID']);
                    break;
                case 'status':
                    $value = self::getStatus($article['active'], $article['instock'], $article['laststock']);
                    break;
                case 'groesse':
                    $size = self::getSizeOptionByArticleDetailId($article['detailID']);
                    $value = !empty($size) ? $size : 'onesize';
                    break;
                case 'beschreibung':
                    $withNewLines = self::getValue($article, $field);
                    $value = str_replace(["\r\n", "\r", "\n"], '<br>', $withNewLines);
                    break;
                case 'beschreibung1':
                    $withNewLines = self::getValue($article, 'beschreibung');
                    $withOutNewLines = str_replace(["\r\n", "\r", "\n"], '<br>', $withNewLines);
                    $wihtOutHtml = strip_tags($withOutNewLines);
                    $value = html_entity_decode($wihtOutHtml);
                    break;
                default:
                    $value = self::getValue($article, $field);
            }
            $line[] = $value;
        }

        return $line;
    }

    /**
     * @param $article
     * @param $field
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return string
     */
    private static function getValue($article, $field)
    {
        $value = $article[$field];
        if ($value) {
            return $value;
        }

        $attrGroup = self::getGroupOrFilterAttribute($field);

        if ($attrGroup) {
            $valuesTreshold = [];
            $attributes = explode(',', $attrGroup);

            foreach ($attributes as $attribute) {
                if(strpos($attribute, "group") !== false) {
                    $groupId = explode('id=', $attribute)[1];
                    array_push($valuesTreshold, self::getConfiguratorGroupValue($article['detailID'], $groupId));
                }
                if(strpos($attribute, "filter") !== false) {
                    $filterId = explode('id=', $attribute)[1];
                    array_push($valuesTreshold, self::getFilterValues($article['articleID'], $filterId));
                }
            }
            return implode("|", array_filter($valuesTreshold));
        }
        return '';
    }

    /**
     * @param $articleId
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return array
     */
    private static function getCategories($articleId)
    {
        $categoryIDs = Shopware()->Db()->query('SELECT categoryID FROM s_articles_categories WHERE articleID = ?', [$articleId])->fetchAll();
        $categoriesList = [];
        foreach ($categoryIDs as $categorieID) {
            $categoryPathResults = self::getCategoriesByParent((int)$categorieID['categoryID']);

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
     * @throws \Doctrine\ORM\ORMException
     * @return array
     */
    private static function getCategoriesByParent($categoryId)
    {
        $pathCategories = Shopware()->Models()->getRepository('Shopware\Models\Category\Category')->getPathById($categoryId, [
            'id',
            'name',
            'parentId'
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

    /**
     * @param  int $articleId
     * @throws \Exception
     * @return string
     */
    private static function getUrl($articleId)
    {
        $baseUrl = self::getFallbackBaseUrl();

        $router = Shopware()->Container()->get('router');
        $assembleParams = [
            'module'    => 'frontend',
            'sViewport' => 'detail',
            'sArticle'  => $articleId
        ];

        $link = $router->assemble($assembleParams);

        return str_replace('http://localhost', $baseUrl, $link);
    }

    /**
     * @param  int $articleId
     * @throws \Exception
     * @return string
     */
    private static function getImages($articleId)
    {
        $sql = 'SELECT img, extension FROM s_articles_img WHERE articleID = ?';
        $images = Shopware()->Db()->query($sql, [$articleId])->fetchAll();

        /** @var MediaService $mediaService */
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        foreach ($images as $image) {
            $path = 'media/image/' . $image['img'] . '.' . $image['extension'];
            if ($mediaService->has($path)) {
                $urlArray[] = $mediaService->getUrl($path);
            }
        }

        $urlString = implode(' | ', $urlArray);

        return $urlString;
    }

    private static function getStatus($active, $instock, $laststock)
    {
        if ($active && (!$laststock || $instock > 0)) {
            return '1';
        }

        return '0';
    }

    /**
     * @param $type
     * @param $field
     * @return string
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    private static function getGroupOrFilterAttribute($field) {
        $query = 'SELECT shopwareAttribute as groupId
                      FROM 8s_attribute_mapping
                      WHERE (shopwareAttribute LIKE "%group%" OR shopwareAttribute LIKE "%filter%")
                      AND eightselectAttribute = "' . $field . '"';

        return Shopware()->Db()->query($query)->fetchColumn();
    }

    /**
     * @param $articleId
     * @param $groupId
     * @return string
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    private static function getConfiguratorGroupValue($detailId, $groupId)
    {
        $sql = 'SELECT s_article_configurator_options.name as name
                FROM s_article_configurator_options
                INNER JOIN s_article_configurator_option_relations on s_article_configurator_option_relations.option_id = s_article_configurator_options.id
                WHERE s_article_configurator_option_relations.article_id = ' . $detailId . '
                AND s_article_configurator_options.group_id = ' . $groupId;

        return Shopware()->Db()->query($sql)->fetchColumn();
    }

    /**
     * @param int $articleId
     * @return array
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    private static function getNonSizeConfiguratorOptionsByArticleDetailId($articleId)
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

        return array_column(Shopware()->Db()->query($sql)->fetchAll(\Zend_Db::FETCH_ASSOC), 'value');
    }


    /**
     * @param int $articleId
     * @return string
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    private static function getSizeOptionByArticleDetailId($articleId)
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

        return Shopware()->Db()->query($sql)->fetchColumn();
    }

    /**
     * @param $articleId
     * @param $filterId
     * @return string
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    private static function getFilterValues($articleId, $filterId)
    {
        $sql = 'SELECT s_filter_values.value as name
                FROM s_filter_values
                INNER JOIN s_filter_articles on s_filter_articles.valueID = s_filter_values.id
                WHERE s_filter_articles.articleID = ' . $articleId . '
                AND s_filter_values.optionID = ' . $filterId;
        $value = Shopware()->Db()->query($sql)->fetchAll();

        return implode(' | ', array_column($value, 'name'));
    }

    /**
     * @return string
     * @throws \Exception
     */
    private static function getFallbackBaseUrl()
    {
        $container = Shopware()->Container();

        if ($container->has('Shop')) {
            /** @var Shop $shop */
            $shop = $container->get('Shop');
        } else {
            /** @var Shop $shop */
            $shop = $container->get('models')->getRepository(Shop::class)->getActiveDefault();
        }

        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        $shopwareInstance = Shopware();
        $versionArray = explode('.', $shopwareInstance::VERSION);
        if ($versionArray[0] >= '5' && $versionArray[1] >= '4') {
            if ($shop->getSecure()) {
                return 'https://' . $shop->getHost() . $shop->getBasePath();
            } else {
                return 'http://' . $shop->getHost() . $shop->getBasePath();
            }
        } else {
            if ($shop->getAlwaysSecure()) {
                return 'https://' . $shop->getSecureHost() . $shop->getSecureBasePath();
            } else {
                return 'http://' . $shop->getHost() . $shop->getBasePath();
            }
        }
    }
}
