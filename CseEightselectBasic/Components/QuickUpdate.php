<?php
namespace CseEightselectBasic\Components;

class QuickUpdate
{
    /**
     * @const string
     */
    const STORAGE = 'files/8select/';

    /**
     * @var array
     */
    public $fields = [
        'sku',
        'mastersku',
        'status',
        'streich_preis',
        'angebots_preis',
    ];

    /**
     * @throws \Exception
     */
    public function doCron()
    {
        $articles = $this->getArticles();

        if (count($articles)) {
            $this->writeFile($articles);
            $this->emptyTable();
        }
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return array
     */
    protected function getArticles()
    {
        $mapping = 'SELECT GROUP_CONCAT(CONCAT(shopwareAttribute," AS ",eightselectAttribute)) as resultMapping FROM 8s_attribute_mapping WHERE shopwareAttribute != "-"';
        $resultMapping = Shopware()->Db()->query($mapping)->fetch(\PDO::FETCH_ASSOC)['resultMapping'];

        $sql = 'SELECT DISTINCT ' . $resultMapping . ',
                s_articles.id as articleID,
                s_articles_details.kind AS mastersku,
                s_articles_prices.price AS angebots_preis,
                s_articles_prices.pseudoprice AS streich_preis,
                s_articles_details.active AS status,
                s_core_tax.tax AS tax
                FROM s_articles
                INNER JOIN s_articles_details ON s_articles.main_detail_id = s_articles_details.id
                INNER JOIN s_articles_attributes ON s_articles_attributes.articledetailsID = s_articles_details.id
                INNER JOIN s_articles_prices ON s_articles_prices.articledetailsID = s_articles_details.id AND s_articles_prices.from = \'1\'
                INNER JOIN 8s_articles_details_change_queue ON 8s_articles_details_change_queue.s_articles_details_id = s_articles_details.id
                INNER JOIN s_core_tax ON s_core_tax.id = s_articles.taxID
                ORDER BY s_articles.id';

        return Shopware()->Db()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $articles
     * @throws \Exception
     */
    protected function writeFile($articles)
    {
        $config = Shopware()->Config();
        $feedId = $config->get('8s_feed_id');
        $feedType = 'product_update';
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

        foreach ($articles as $article) {
            $line = $this->getLine($article);
            fputcsv($fp, $line, ';');
        }

        fclose($fp);

        AWSUploader::upload($filename, self::STORAGE, $feedId, $feedType);
    }

    protected function emptyTable()
    {
        $sql = 'DELETE FROM 8s_articles_details_change_queue';

        Shopware()->Db()->query($sql);
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

        foreach ($this->fields as $field) {
            switch ($field) {
                case 'streich_preis':
                case 'angebots_preis':
                    $line[] = PriceHelper::getGrossPrice($article, $field);
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
}
