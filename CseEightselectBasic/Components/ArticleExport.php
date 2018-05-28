<?php
namespace CseEightselectBasic\Components;

class ArticleExport
{
    const STORAGE = 'files/8select/';

    const CRON_NAME = '8select Full Export';

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
     * @throws \Enlight_Exception
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public function checkRunOnce()
    {
        $queueSql = 'SELECT * from 8s_cron_run_once_queue WHERE running = 0 AND cron_name = "' . self::CRON_NAME . '"';
        $runningSql = 'SELECT * from 8s_cron_run_once_queue WHERE running = 1 AND cron_name = "' . self::CRON_NAME . '"';
        $queue = Shopware()->Db()->query($queueSql)->fetchAll(\PDO::FETCH_ASSOC);
        $running = Shopware()->Db()->query($runningSql)->fetchAll(\PDO::FETCH_ASSOC);

        if (count($queue) && !count($running)) {
            $id = reset($queue)['id'];
            $sqls = [
                'UPDATE 8s_cron_run_once_queue SET running = 1 WHERE id = ' . $id,
                'DELETE from 8s_cron_run_once_queue WHERE running = 0 AND cron_name = "' . self::CRON_NAME . '"',
            ];
            foreach ($sqls as $sql) {
                Shopware()->Db()->query($sql);
            }
            $this->doCron($id);
            $this->emptyQueueTable();
        }
    }

    /**
     * @param  null $queueId
     * @throws \Doctrine\ORM\ORMException
     * @throws \Enlight_Exception
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public function doCron($queueId = null)
    {
        if ($queueId == null) {
            $connection = Shopware()->Container()->get('dbal_connection');
            $connection->insert('8s_cron_run_once_queue', ['cron_name' => '8select Full Export']);
            $this->checkRunOnce();

            return;
        }

        $start = time();
        $config = Shopware()->Config();
        $feedId = $config->get('8s_feed_id');
        $feedType = 'product_feed';
        $timestampInMillis = round(microtime(true) * 1000);
        $filename = sprintf('%s_%s_%d.csv', $feedId, $feedType, $timestampInMillis);

        if (!is_dir(self::STORAGE)) {
            mkdir(self::STORAGE, 0775, true);
        }

        $fp = fopen(self::STORAGE . $filename, 'a');

        $header = [];
        foreach ($this->fields as $field) {
            $header[] = $field;
        }

        fputcsv($fp, $header, ';');

        $this->writeFile($fp, $queueId);

        fclose($fp);

        AWSUploader::upload($filename, self::STORAGE, $feedId, $feedType);

        if ($this::DEBUG) {
            echo('Process completed in ' . (time() - $start) . "s\n");
        }
    }

    /**
     * @param $fp
     * @param $queueId
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    protected function writeFile($fp, $queueId)
    {
        $attributeMappingQuery = 'SELECT GROUP_CONCAT(CONCAT(shopwareAttribute," AS ",eightselectAttribute)) as resultMapping
                         FROM 8s_attribute_mapping
                         WHERE shopwareAttribute != "-"
                         AND shopwareAttribute NOT LIKE "%id=%"';

        $attributeMapping = Shopware()->Db()->query($attributeMappingQuery)->fetch(\PDO::FETCH_ASSOC)['resultMapping'];

        $numArticles = $this->getNumArticles();
        $batchSize = 100;

        for ($i = 0; $i < $numArticles; $i += $batchSize) {
            $this->updateStatus($numArticles, $i, $queueId);

            $articles = $this->getArticles($attributeMapping, $i, $batchSize);

            if ($this::DEBUG) {
                $top = $i + ($batchSize - 1);
                if ($top > $numArticles) {
                    $top = $numArticles;
                }
                echo('Processing articles ' . $i . ' to ' . $top . "\n");
            }

            foreach ($articles as $article) {
                $line = FieldHelper::getLine($article, $this->fields);
                fputs($fp, implode(';', $line) . "\r\n");
            }
        }
    }

    /**
     * @param string $mapping
     * @param int $number
     * @param int $from
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return array
     */
    protected function getArticles($mapping, $from, $number)
    {
        $sql = 'SELECT ' . $mapping . ',
                s_articles.id as articleID,
                s_articles.laststock AS laststock,
                s_articles_details.id as detailID,
                s_articles_prices.price AS angebots_preis,
                s_articles_prices.pseudoprice AS streich_preis,
                s_articles_details.active AS active,
                s_articles_details.instock AS instock,
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
        $articles = Shopware()->Db()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        return $articles;
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return mixed
     */
    protected function getNumArticles()
    {
        $sql = 'SELECT id FROM s_articles_details';

        return Shopware()->Db()->query($sql)->rowCount();
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     */
    private function emptyQueueTable()
    {
        $sql = 'DELETE FROM 8s_cron_run_once_queue WHERE cron_name = "' . self::CRON_NAME . '"';
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
        $progress = floor($currentArticle / $numArticles * 100);

        if ($queueId && $progress != $this->currentProgress) {
            $sql = 'UPDATE 8s_cron_run_once_queue SET progress = ' . $progress . ' WHERE id = ' . $queueId;
            Shopware()->Db()->query($sql);
            $this->currentProgress = $progress;
        }
    }
}
