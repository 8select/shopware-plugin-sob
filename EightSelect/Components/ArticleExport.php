<?php
namespace EightSelect\Components;

use Aws\S3\S3Client;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Image;

class ArticleExport
{
    const STORAGE = 'files/export/';

    public $fields = [
        'sku',
        'mastersku',
        'status',
        'warenkorb_id',
        'ean',
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
        'produkt_url',
        'bilder',
        'beschreibung',
        'beschreibung1',
        'beschreibung2',
    ];

    /**
     * @var \Shopware\Models\Article\Repository
     */
    protected $repository;

    protected function uploadToAWS($filename)
    {
        // Instantiate an Amazon S3 client.
        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => 'eu-central-1',
        ]);

        try {
            $s3->putObject([
                'Bucket' => '8sdemo.1drop.de',
                'Key'    => 'my-object',
                'Body'   => fopen($filename, 'r'),
                'ACL'    => 'public-read',
            ]);
        } catch (\Aws\S3\Exception\S3Exception $e) {
            echo "There was an error uploading the file.\n";
        }
    }

    public function doCron()
    {
        if (!is_dir(self::STORAGE)) {
            mkdir(self::STORAGE, 775, true);
        }

        $filename = self::STORAGE . 'products_full_' . date('YmdHis') . '.csv';
        $fp = fopen($filename, 'a');

        $header = [];
        foreach ($this->fields as $field) {
            $header[] = $field;
        }

        fputcsv($fp, $header, ';');

        $articles = $this->getArticles();

        foreach ($articles as $article) {
            $line = $this->getLine($article);
            fputcsv($fp, $line, ';');
        }

        fclose($fp);
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return array
     */
    protected function getArticles()
    {
        $mapping = 'SELECT GROUP_CONCAT(CONCAT(shopwareAttribute," AS ",eightSelectAttribute)) as resultMapping FROM es_attribute_mapping WHERE shopwareAttribute != "-"';
        $resultMapping = Shopware()->Db()->query($mapping)->fetch(\PDO::FETCH_ASSOC)['resultMapping'];

        $sql = 'SELECT ' . $resultMapping . ',
                s_articles.id as articleID,
                s_articles_details.kind AS mastersku,
                s_articles_prices.price AS angebots_preis,
                s_articles_prices.pseudoprice AS streich_preis,
                s_articles_details.active AS status,
                s_articles_supplier.name as marke
                FROM s_articles
                INNER JOIN s_articles_details ON s_articles.main_detail_id = s_articles_details.id
                INNER JOIN s_articles_attributes ON s_articles_attributes.articledetailsID = s_articles_details.id
                INNER JOIN s_articles_prices ON s_articles_prices.articledetailsID = s_articles_details.id
                INNER JOIN s_articles_supplier ON s_articles_supplier.id = s_articles.supplierID
                ORDER BY s_articles.id';

        return Shopware()->Db()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getLine($article)
    {
        $line = [];

        /** @var array $articleData */
        $articleData = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, $article['articleID'], true);
        $categories = $this->getCategories($articleData);

        foreach ($this->fields as $field) {
            switch ($field) {
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
                    if ($article[$field] == 0) {
                        $line[] = $article['angebots_preis'];
                    } else {
                        $line[] = $article[$field];
                    }
                    break;
                case 'produkt_url':
                    $line[] = $this->getUrl($articleData);
                    break;
                case 'bilder':
                    $line[] = $this->getImages($articleData);
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
     * @param $articleData
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return array
     */
    private function getCategories($articleData)
    {
        $categoryIDs = Shopware()->Db()->query('SELECT categoryID FROM s_articles_categories WHERE articleID = ?', [$articleData['articleID']])->fetchAll();
        $categoriesList = [];
        foreach ($categoryIDs as $categorieID) {
            $categoryPathResults = array_reverse(Shopware()->Modules()->Categories()->sGetCategoriesByParent((int) $categorieID['categoryID']));

            $categoryNames = [];
            foreach ($categoryPathResults as $categoryPathResult) {
                $categoryNames[] = $categoryPathResult['name'];
            }

            $categoriesList[] = implode(' ', $categoryNames);
        }

        return $categoriesList;
    }

    /**
     * @param  array  $articleData
     * @return string
     */
    private function getUrl($articleData)
    {
        return Shopware()->Front()->Router()->assemble([
            'controller' => 'detail',
            'action'     => 'index',
            'sArticle'   => $articleData['articleID'],
        ]);
    }

    /**
     * @param  array      $articleData
     * @throws \Exception
     * @return string
     */
    private function getImages($articleData)
    {
        /** @var \Shopware\Components\Model\ModelManager $em */
        $em = Shopware()->Container()->get('models');
        /** @var Article $article */
        $article = $em->getRepository(Article::class)->find((int) $articleData['articleID']);

        /** @var MediaService $mediaService */
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');

        $urlArray = [];
        /** @var Image $image */
        foreach ($article->getImages() as $image) {
            if ($mediaService->has($image->getMedia()->getPath())) {
                $urlArray[] = $mediaService->getUrl($image->getMedia()->getPath());
            }
        }

        $urlString = implode('|', $urlArray);
        return $urlString;
    }
}
