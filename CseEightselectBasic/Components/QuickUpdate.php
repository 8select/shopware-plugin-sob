<?php
namespace CseEightselectBasic\Components;

use League\Csv\Writer;

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
     * @const string
     */
    const CRON_NAME = '8select Quick Export';

    /**
     * @var int
     */
    private $queueId = null;

    /**
     * @var int
     */
    private $currentProgress = null;

    /**
     * @throws \Exception
     */
    public function doCron()
    {
        if ($this->getNumArticles()) {
            $this->setProgressTable();

            $config = Shopware()->Config();
            $feedId = $config->get('8s_feed_id');
            $feedType = 'status_feed';
            $timestampInMillis = round(microtime(true) * 1000);
            $filename = sprintf('%s_%s_%d.csv', $feedId, $feedType, $timestampInMillis);

            if (!is_dir(self::STORAGE)) {
                mkdir(self::STORAGE, 775, true);
            }

            if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                require_once __DIR__ . '/../vendor/autoload.php';
            }

            $csvWriter = Writer::createFromPath(self::STORAGE . $filename, 'a');
            $csvWriter->setDelimiter(';');

            $header = [];
            foreach ($this->fields as $field) {
                $header[] = $field;
            }
            $csvWriter->insertOne($header);

            $this->writeFile($csvWriter);

            AWSUploader::upload($filename, self::STORAGE, $feedId, $feedType);
        }

        $this->emptyTables();
    }

    /**
     * @param Writer $csvWriter
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    protected function writeFile(Writer $csvWriter)
    {
        $numArticles = $this->getNumArticles();
        $batchSize = 100;

        for ($i = 0; $i < $numArticles; $i += $batchSize) {
            $this->updateStatus($numArticles, $i);

            $articles = $this->getArticles($i, $batchSize);

            foreach ($articles as $article) {
                $line = FieldHelper::getLine($article, $this->fields);
                $csvWriter->insertOne($line);
            }
        }
    }

    /**
     * @param $number
     * @param $from
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return array
     */
    protected function getArticles($from, $number)
    {
        $sql = 'SELECT DISTINCT
                s_articles.id as articleID,
                s_articles_prices.price AS angebots_preis,
                s_articles_prices.pseudoprice AS streich_preis,
                s_articles_details.id AS detailID,
                s_articles_details.active AS active,
                s_articles_details.instock AS instock,
                s_articles_details.ordernumber as sku,
                s_core_tax.tax AS tax
                FROM s_articles_details
                INNER JOIN s_articles ON s_articles.id = s_articles_details.articleID
                INNER JOIN s_articles_attributes ON s_articles_attributes.articledetailsID = s_articles_details.id
                INNER JOIN s_articles_prices ON s_articles_prices.articledetailsID = s_articles_details.id AND s_articles_prices.from = \'1\'
                INNER JOIN 8s_articles_details_change_queue ON 8s_articles_details_change_queue.s_articles_details_id = s_articles_details.id
                INNER JOIN s_core_tax ON s_core_tax.id = s_articles.taxID
                LIMIT ' . $number . ' OFFSET ' . $from;
        $attributes = Shopware()->Db()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        return $attributes;
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return mixed
     */
    protected function getNumArticles()
    {
        $sql = 'SELECT DISTINCT s_articles_details_id FROM 8s_articles_details_change_queue';

        return Shopware()->Db()->query($sql)->rowCount();
    }

    /**
     * @param $numArticles
     * @param $currentArticle
     * @throws \Zend_Db_Adapter_Exception
     */
    private function updateStatus($numArticles, $currentArticle)
    {
        $progress = floor($currentArticle / $numArticles * 100);

        if ($this->queueId && $progress != $this->currentProgress) {
            $sql = 'UPDATE 8s_cron_run_once_queue SET progress = ' . $progress . ' WHERE id = ' . $this->queueId;
            Shopware()->Db()->query($sql);
            $this->currentProgress = $progress;
        }
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     */
    protected function emptyTables()
    {
        $sqls = [
            'DELETE FROM 8s_articles_details_change_queue',
            'DELETE FROM 8s_cron_run_once_queue WHERE cron_name = "' . self::CRON_NAME . '"',
        ];

        foreach ($sqls as $sql) {
            Shopware()->Db()->query($sql);
        }

        $this->queueId = null;
        $this->currentProgress = null;
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    protected function setProgressTable()
    {
        $queueSql = 'SELECT * from 8s_cron_run_once_queue WHERE running = 0 AND cron_name = "' . self::CRON_NAME . '"';
        $runningSql = 'SELECT * from 8s_cron_run_once_queue WHERE running = 1 AND cron_name = "' . self::CRON_NAME . '"';
        $queue = Shopware()->Db()->query($queueSql)->fetchAll(\PDO::FETCH_ASSOC);
        $running = Shopware()->Db()->query($runningSql)->fetchAll(\PDO::FETCH_ASSOC);

        if (!count($queue)) {
            $connection = Shopware()->Container()->get('dbal_connection');
            $connection->insert('8s_cron_run_once_queue', ['cron_name' => self::CRON_NAME]);
            $this->setProgressTable();
        } elseif (count($queue) && !count($running)) {
            $id = reset($queue)['id'];
            $sqls = [
                'UPDATE 8s_cron_run_once_queue SET running = 1 WHERE id = ' . $id,
                'DELETE from 8s_cron_run_once_queue WHERE running = 0 AND cron_name = "' . self::CRON_NAME . '"',
            ];
            foreach ($sqls as $sql) {
                Shopware()->Db()->query($sql);
            }
            $this->queueId = $id;
        }
    }
}
