<?php
namespace CseEightselectBasic\Components;

use Shopware\Bundle\MediaBundle\MediaService;

class ArticleExport
{
    /**
     * @const string
     */
    const STORAGE = 'files/8select/';

    /** @var bool  */
    const DEBUG = false;

    private $currentProgress = 0;

    /**
     * @var array
     */
    public $fields = [
        'sku',
        'mastersku',
        'status',
        'ean',
        'model',
        'name1',
        'name2',
        'kategorie1',
        'kategorie2',
        'kategorie3',
        'streich_preis',
        'angebots_preis',
        'groesse',
        'marke',
        'bereich',
        'rubrik',
        'abteilung',
        'kiko',
        'typ',
        'farbe',
        'farbspektrum',
        'absatzhoehe',
        'muster',
        'aermellaenge',
        'kragenform',
        'obermaterial',
        'passform',
        'schnitt',
        'waschung',
        'stil',
        'sportart',
        'detail',
        'auspraegung',
        'baukasten',
        'eigenschaft',
        'fuellmenge',
        'funktion',
        'gruppe',
        'material',
        'saison',
        'serie',
        'verschluss',
        'produkt_url',
        'bilder',
        'beschreibung',
        'beschreibung1',
        'beschreibung2',
        'sonstiges',
    ];

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public function checkRunOnce()
    {
        $sql = 'SELECT * from 8s_cron_run_once_queue WHERE running = 0';
        $queue = Shopware()->Db()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        if (count($queue)) {
            $id = reset($queue)['id'];
            $sqls = [
                'UPDATE 8s_cron_run_once_queue SET running = 1 WHERE id = ' . $id,
                'DELETE from 8s_cron_run_once_queue WHERE running = 0'
            ];
            foreach ($sqls as $sql) {
                Shopware()->Db()->query($sql);
            }
            $this->doCron($id);
            $this->emptyQueueTable();
        }
    }

    /**
     * @param null $queuId
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public function doCron($queuId = null)
    {
        $start = time();
        $config = Shopware()->Config();
        $feedId = $config->get('8s_feed_id');
        $feedType = 'product_feed';
        $filename = $feedId . '_' . $feedType . '_' . time() . '.csv';

        if (!is_dir(self::STORAGE)) {
            mkdir(self::STORAGE, 775, true);
        }

        $fp = fopen(self::STORAGE . $filename, 'a');

        $header = [];
        foreach ($this->fields as $field) {
            $header[] = $field;
        }

        fputcsv($fp, $header, ';');

        $this->writeFile($fp, $queuId);

        fclose($fp);

        AWSUploader::upload($filename, self::STORAGE, $feedId, $feedType);

        if ($this::DEBUG) {
            echo("Process completed in " . (time() - $start) . "s\n");
        }
    }

    /**
     * @param $fp
     * @param $queueId
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    protected function writeFile ($fp, $queueId) {
        $attributeMappingQuery = 'SELECT GROUP_CONCAT(CONCAT(shopwareAttribute," AS ",eightselectAttribute)) as resultMapping 
                         FROM 8s_attribute_mapping 
                         WHERE shopwareAttribute != "-"
                         AND shopwareAttribute NOT LIKE "%id=%"';

        $attributeMapping = Shopware()->Db()->query($attributeMappingQuery)->fetch(\PDO::FETCH_ASSOC)['resultMapping'];

        $numArticles = $this->getNumArticles();
        $batchSize = 100;

        for ($i = 0; $i < $numArticles; $i+=$batchSize ) {
            $this->updateStatus($numArticles, $i, $queueId);

            $articles = $this->getArticles($attributeMapping, $i, $batchSize);

            if ($this::DEBUG) {
                $top = $i + ($batchSize - 1);
                if ($top > $numArticles) {
                    $top = $numArticles;
                }
                echo("Processing articles " . $i . " to " . $top . "\n");
            }

            foreach ($articles as $article) {
                $line = $this->getLine($article);
                fputcsv($fp, $line, ';');
            }
        }
    }

    /**
     * @param $number
     * @param $from
     * @return array
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    protected function getArticles($mapping, $from, $number)
    {
        $sql = 'SELECT ' . $mapping . ',
                s_articles.id as articleID,
                s_articles_prices.price AS angebots_preis,
                s_articles_prices.pseudoprice AS streich_preis,
                s_articles_details.active AS status,
                s_articles_supplier.name as marke,
                s_articles_details.ordernumber as sku,
                s_core_tax.tax AS tax
                FROM s_articles_details
                INNER JOIN s_articles ON s_articles.id = s_articles_details.articleID
                INNER JOIN s_articles_attributes ON s_articles_attributes.articledetailsID = s_articles_details.id
                INNER JOIN s_articles_prices ON s_articles_prices.articledetailsID = s_articles_details.id AND s_articles_prices.from = \'1\'
                INNER JOIN s_articles_supplier ON s_articles_supplier.id = s_articles.supplierID
                INNER JOIN s_core_tax ON s_core_tax.id = s_articles.taxID
                LIMIT ' . $number . ' OFFSET ' . $from;
        $attributes = Shopware()->Db()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        return $attributes;
    }

    protected function getConfig($articleId, $groupId) {
        $sql = 'SELECT s_filter_values.value as name
                FROM s_filter_values
                INNER JOIN s_filter_articles on s_filter_articles.valueID = s_filter_values.id
                WHERE s_filter_articles.articleID = ' . $articleId . '
                AND s_filter_values.optionID = ' . $groupId;
        $config = Shopware()->Db()->query($sql)->fetchAll();

        return implode('; ', array_column($config, 'name'));
    }

    /**
     * @return mixed
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    protected function getNumArticles()
    {
        $sql = 'SELECT id FROM s_articles_details';
        return Shopware()->Db()->query($sql)->rowCount();
    }

    /**
     * @param $article
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    private function getLine($article)
    {
        $line = [];

        /** @var array $categories */
        $categories = $this->getCategories($article['articleID']);

        foreach ($this->fields as $field) {
            switch ($field) {
                case 'mastersku':
                    $line[] = $this->getMasterSku($article['articleID']);
                    break;
                case 'kategorie1':
                    $line[] = !empty($categories[0]) ? $categories[0] : '';
                    break;
                case 'kategorie2':
                    $line[] = !empty($categories[1]) ? $categories[1] : '';
                    break;
                case 'kategorie3':
                    $line[] = !empty($categories[2]) ? $categories[2] : '';
                    break;
                case 'streich_preis':
                case 'angebots_preis':
                    $line[] = PriceHelper::getGrossPrice($article, $field);
                    break;
                case 'produkt_url':
                    $line[] = $this->getUrl($article['articleID']);
                    break;
                case 'bilder':
                    $line[] = $this->getImages($article['articleID']);
                    break;
                default:
                    $line[] = $this->getValue($article, $field);
            }
        }

        return $line;
    }

    /**
     * @param $article
     * @param $field
     * @return string
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    protected function getValue($article, $field) {
        $value = $article[$field];
        if ($value) {
            return $value;
        } else {
            $query = 'SELECT shopwareAttribute as groupId
                      FROM 8s_attribute_mapping
                      WHERE shopwareAttribute LIKE "%id=%"
                      AND eightselectAttribute = "' . $field . '"';
            $config = Shopware()->Db()->query($query)->fetchColumn();

            if ($config) {
                $groupId = explode('id=', $config)[1];
                if ($groupId) {
                    return $this->getConfig($article['articleID'], $groupId);
                }
            }
            return '';
        }
    }

    /**
     * @param $articleId
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return array
     */
    private function getCategories($articleId)
    {
        $categoryIDs = Shopware()->Db()->query('SELECT categoryID FROM s_articles_categories WHERE articleID = ?', [$articleId])->fetchAll();
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
     * @throws \Doctrine\ORM\ORMException
     * @return array
     */
    private function getCategoriesByParent($categoryId)
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
    private function getUrl($articleId)
    {
        return Shopware()->Container()->get('router')->assemble([
            'controller' => 'detail',
            'action'     => 'index',
            'sArticle'   => $articleId,
        ]);
    }

    /**
     * @param  int        $articleId
     * @throws \Exception
     * @return string
     */
    private function getImages($articleId)
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

        $urlString = implode('|', $urlArray);
        return $urlString;
    }

    /**
     * @param $articleId
     * @return mixed
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    private function getMasterSku($articleId)
    {
        $sql = 'SELECT ordernumber FROM s_articles_details WHERE articleID = ? AND kind = "1"';
        $mainDetail = Shopware()->Db()->query($sql, [$articleId])->fetch();

        return $mainDetail['ordernumber'];
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     */
    private function emptyQueueTable()
    {
        $sql = 'DELETE FROM 8s_cron_run_once_queue';
        Shopware()->Db()->query($sql);
    }

    /**
     * @param $numArticles
     * @param $currentArticle
     * @param $queueId
     * @throws \Zend_Db_Adapter_Exception
     */
    private function updateStatus($numArticles, $currentArticle, $queueId)
    {
        $progress = floor($currentArticle/$numArticles * 100);

        if ($queueId && $progress != $this->currentProgress) {
            $sql = 'UPDATE 8s_cron_run_once_queue SET progress = ' . $progress . ' WHERE id = ' . $queueId;
            Shopware()->Db()->query($sql);
            $this->currentProgress = $progress;
        }
    }
}
