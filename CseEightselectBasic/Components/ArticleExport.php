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
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public function doCron()
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

        $this->writeFile($fp);

        fclose($fp);

        AWSUploader::upload($filename, self::STORAGE, $feedId, $feedType);

        if ($this::DEBUG) {
            echo("Process completed in " . (time() - $start) . "s\n");
        }
    }

    /**
     * @param $fp
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    protected function writeFile ($fp) {
        $mappingQuery = 'SELECT GROUP_CONCAT(CONCAT(shopwareAttribute," AS ",eightselectAttribute)) as resultMapping FROM 8s_attribute_mapping WHERE shopwareAttribute != "-"';
        $mapping = Shopware()->Db()->query($mappingQuery)->fetch(\PDO::FETCH_ASSOC)['resultMapping'];
        $numArticles = $this->getNumArticles();
        $batchSize = 100;

        for ($i = 0; $i < $numArticles; $i+=$batchSize ) {
            $articles = $this->getArticles($mapping, $i, $batchSize);

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

        return Shopware()->Db()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
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
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @throws \Exception
     * @return array
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
                    $value = $article[$field];
                    if ($value) {
                        $line[] = $value;
                    } else {
                        $line[] = '';
                    }
            }
        }

        return $line;
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
}
