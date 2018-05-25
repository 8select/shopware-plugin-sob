<?php
namespace CseEightselectBasic\Components;

use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Models\Shop\Shop;

class FieldHelper
{
    /**
     * @param $article
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return array
     */
    public static function getLine($article, $fields)
    {
        $line = [];

        /** @var array $categories */
        $categories = self::getCategories($article['articleID']);

        foreach ($fields as $field) {
            switch ($field) {
                case 'mastersku':
                    $value = self::getMasterSku($article['articleID']);
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
                default:
                    $value = self::getValue($article, $field);
            }
            $line[] = self::formatString($value);
        }

        return $line;
    }

    /**
     * @param $string
     * @return mixed|string
     */
    private static function formatString($string)
    {
        if ($string === '') {
            return $string;
        }

        $string = trim(preg_replace('/\s+/', ' ', $string));
        $string = str_replace('\\"', '"', $string);
        $string = str_replace('"', '\"', $string);
        return '"' . $string . '"';
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

        $configGroup = self::getGroupOrFilterAttribute('group', $field);

        if ($configGroup) {
            $groupId = explode('id=', $configGroup)[1];
            return self::getConfiguratorGroupValue($article['detailID'], $groupId);
        }

        $filterGroup = self::getGroupOrFilterAttribute('filter', $field);

        if ($filterGroup) {
            $filterId = explode('id=', $filterGroup)[1];
            return self::getFilterValues($article['articleID'], $filterId);
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
            $categoryPathResults = self::getCategoriesByParent((int) $categorieID['categoryID']);

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
        $pathCategories = Shopware()->Models()->getRepository('Shopware\Models\Category\Category')->getPathById($categoryId, ['id', 'name', 'parentId']);
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
     * @param  int        $articleId
     * @throws \Exception
     * @return string
     */
    private static function getUrl($articleId)
    {
        $baseUrl = self::getFallbackBaseUrl();

        $router = Shopware()->Container()->get('router');
        $assembleParams = array(
            'module' => 'frontend',
            'sViewport' => 'detail',
            'sArticle' => $articleId
        );

        $link = $router->assemble($assembleParams);
        return str_replace('http://localhost', $baseUrl, $link);
    }

    /**
     * @param  int        $articleId
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

    /**
     * @param $articleId
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return mixed
     */
    private static function getMasterSku($articleId)
    {
        $sql = 'SELECT ordernumber FROM s_articles_details WHERE articleID = ? AND kind = "1"';
        $mainDetail = Shopware()->Db()->query($sql, [$articleId])->fetch();

        return $mainDetail['ordernumber'];
    }

    /**
     * @param $type
     * @param $field
     * @return string
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    private static function getGroupOrFilterAttribute($type, $field) {
        $query = 'SELECT shopwareAttribute as groupId
                      FROM 8s_attribute_mapping
                      WHERE shopwareAttribute LIKE "%' . $type . '%"
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

        if ($shop->getAlwaysSecure()) {
            return 'https://' . $shop->getSecureHost() . $shop->getSecureBasePath();
        } else {
            return 'http://' . $shop->getHost() . $shop->getBasePath();
        }
    }
}
