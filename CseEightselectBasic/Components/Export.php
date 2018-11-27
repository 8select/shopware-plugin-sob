<?php
namespace CseEightselectBasic\Components;

use League\Csv\Writer;
use CseEightselectBasic\Components\Config;
use CseEightselectBasic\Components\ConfigValidator;
use CseEightselectBasic\Components\FeedLogger;
use CseEightselectBasic\Components\RunCronOnce;

abstract class Export
{
    /**
     * @var integer
     */
    private $currentProgress = 0;

    /**
     * @var string[]
     */
    protected $header = [];

    /**
     * @var string[]
     */
    protected $fields = [];

    public function scheduleCron()
    {
        RunCronOnce::runOnce(static::CRON_NAME);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Enlight_Exception
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     */
    public function doCron()
    {
        try {
            $start = time();
            if ($this->canRunCron() === false) {
                RunCronOnce::finishCron(static::CRON_NAME);
                return;
            }

            $message = sprintf('Führe %s aus.', static::CRON_NAME);
            Shopware()->PluginLogger()->info($message);
            if (getenv('ES_DEBUG')) {
                echo $message . \PHP_EOL;
            }

            RunCronOnce::runCron(static::CRON_NAME);
            $this->generateExportCSV();
            $this->emptyQueue();
            FeedLogger::logFeed(static::CRON_NAME);
            RunCronOnce::finishCron(static::CRON_NAME);

            $message = sprintf('%s abgeschlossen in %d s', static::CRON_NAME, (time() - $start));
            Shopware()->PluginLogger()->info($message);
            if (getenv('ES_DEBUG')) {
                echo $message;
            }
        } catch (\Exception $exception) {
            Shopware()->PluginLogger()->error($exception);
            RunCronOnce::finishCron(static::CRON_NAME);
            if (getenv('ES_DEBUG')) {
                echo $exception;
            }
            throw $exception;
        }
    }

    /**
     * @return array
     */
    protected function canRunCron() {
        if (!RunCronOnce::isScheduled(static::CRON_NAME)) {
            $message = sprintf('%s nicht ausgeführt, es ist kein Export in der Warteschleife.', static::CRON_NAME);
            if (getenv('ES_DEBUG')) {
                echo $message . \PHP_EOL;
            }
            return false;
        }

        if (!ConfigValidator::isConfigValid()) {
            $message = sprintf('%s nicht ausgeführt, da die Plugin Konfiguration ungültig ist.', static::CRON_NAME);
            Shopware()->PluginLogger()->warning($message);
            if (getenv('ES_DEBUG')) {
                echo $message;
            }

            return false;
        }

        if ($this->getNumArticles() <= 0) {
            $message = sprintf('%s nicht ausgeführt, es wurden keine Produkte für Export gefunden.', static::CRON_NAME);
            if (getenv('ES_DEBUG')) {
                echo $message . \PHP_EOL;
            }

            return false;
        }

        if (RunCronOnce::isRunning(static::CRON_NAME)) {
            $message = sprintf('%s nicht ausgeführt, es läuft bereits ein Export.', static::CRON_NAME);
            if (getenv('ES_DEBUG')) {
                echo $message . \PHP_EOL;
            }
            return false;
        }

        return true;
    }

    private function generateExportCSV() {
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            require_once __DIR__ . '/../vendor/autoload.php';
        }

        try {
            $path = $this->createTempFile();
        } catch (\Exception $exception) {
            Shopware()->PluginLogger()->error($exception->getMessage());
            if (getenv('ES_DEBUG')) {
                echo $message . \PHP_EOL;
            }
            return false;
        }

        $csvWriter = Writer::createFromPath($path, 'w');
        $csvWriter->setDelimiter(';');

        $csvWriter->insertOne($this->header);

        $this->writeFile($csvWriter);

        AWSUploader::upload($path, static::FEED_TYPE);
        unlink($path);
    }

    private function createTempFile()
    {
        try {
            $tempfile = tmpfile();

            if (!$tempfile) {
                $message = sprintf('%s nicht ausgeführt, temporäre Datei für Export konnte nicht erstellt werden.', static::CRON_NAME);
                if (getenv('ES_DEBUG')) {
                    echo $message . \PHP_EOL;
                }
                throw new \Exception($message);
            }
            $path = stream_get_meta_data($tempfile)['uri'];
        } catch (\Exception $exception) {
            $storagePath = Shopware()->DocPath('files_8select');
            $isDirCreated = true;
            if (!is_dir($storagePath)) {
                $isDirCreated = mkdir($storagePath, 0775, true);
            }
            if (!$isDirCreated) {
                $message = sprintf('%s nicht ausgeführt, Fallback Verzeichnis für Export konnte nicht erstellt werden.', static::CRON_NAME);
                if (getenv('ES_DEBUG')) {
                    echo $message . \PHP_EOL;
                }
                throw new \Exception($message, 500, $exception);
            }
            $path = tempnam($storagePath, static::FEED_TYPE);
        }

        $tempPath = new \SplFileInfo($path);
        if (!$tempPath->isWritable()) {
            $message = sprintf('%s nicht ausgeführt, temporäre Datei in Fallback Verzeichnis für Export konnte nicht erstellt werden.', static::CRON_NAME);
            if (getenv('ES_DEBUG')) {
                echo $message . \PHP_EOL;
            }
            throw new \Exception($message);
        }
        return $path;
    }

    /**
     * @param Writer $csvWriter
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @throws \League\Csv\CannotInsertRecord
     */
    private function writeFile(Writer $csvWriter)
    {
        $attributeMappingQuery = 'SELECT GROUP_CONCAT(CONCAT(shopwareAttribute," AS ",eightselectAttribute)) as resultMapping
                         FROM 8s_attribute_mapping
                         WHERE shopwareAttribute != "-"
                         AND shopwareAttribute != ""
                         AND shopwareAttribute IS NOT NULL
                         AND shopwareAttribute NOT LIKE "%id=%"';

        $attributeMapping = Shopware()->Db()->query($attributeMappingQuery)->fetch(\PDO::FETCH_ASSOC)['resultMapping'];

        $numArticles = $this->getNumArticles();
        $batchSize = 100;

        for ($i = 0; $i < $numArticles; $i += $batchSize) {
            $articles = $this->getArticles($attributeMapping, $i, $batchSize);

            $top = $i + ($batchSize - 1);
            if ($top > $numArticles) {
                $top = $numArticles;
            }

            if (getenv('ES_DEBUG')) {
                echo sprintf('Processing articles %d to %d from %d%s', $i, $top, $numArticles, \PHP_EOL);
            }

            foreach ($articles as $article) {
                $line = FieldHelper::getLine($article, $this->fields);
                $csvWriter->insertOne($line);
            }
            $this->updateStatus($numArticles, $top);
        }
    }

    /**
     * @param string $mapping
     * @param int $offset
     * @param int $limit
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return array
     */
    protected function getArticles($mapping, $offset, $limit)
    {
        $sqlTemplate = 'SELECT %s %s,
                    s_articles_details.articleID,
                    s_articles.laststock AS laststock,
                    s_articles_details.id as detailID,
                    s_articles_prices.price AS angebots_preis,
                    s_articles_prices.pseudoprice AS streich_preis,
                    s_articles_details.active AS active,
                    s_articles_details.instock AS instock,
                    s_articles_supplier.name as marke,
                    ad2.ordernumber as mastersku,
                    s_articles_details.ordernumber as sku,
                    s_core_tax.tax AS tax
                FROM s_articles_details
                    %s
                    INNER JOIN s_articles_details AS ad2 ON ad2.articleID = s_articles_details.articleID AND ad2.kind = 1
                    INNER JOIN s_articles ON s_articles.id = s_articles_details.articleID
                    INNER JOIN s_articles_attributes ON s_articles_attributes.articledetailsID = s_articles_details.id
                    INNER JOIN s_articles_prices ON s_articles_prices.articledetailsID = s_articles_details.id AND s_articles_prices.from = 1 AND s_articles_prices.pricegroup = "EK"
                    INNER JOIN s_articles_supplier ON s_articles_supplier.id = s_articles.supplierID
                    INNER JOIN s_core_tax ON s_core_tax.id = s_articles.taxID
                LIMIT %d OFFSET %d';

        $distinct = '';
        $join = '';

        if (static::FEED_TYPE === PropertyExport::FEED_TYPE && $this->isDeltaExport()) {
            $distinct = ' DISTINCT ';
            $join = ' INNER JOIN 8s_articles_details_change_queue ON 8s_articles_details_change_queue.s_articles_details_id = s_articles_details.id ';
        }

        $sql = sprintf($sqlTemplate, $distinct, $mapping, $join, $limit, $offset);

        if (getenv('ES_DEBUG')) {
            echo  \PHP_EOL . 'SQL'  . \PHP_EOL;
            echo $sql . \PHP_EOL;
        }

        $articles = Shopware()->Db()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        return $articles;
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return integer
     */
    protected function getNumArticles()
    {
        $sql = 'SELECT count(*) from s_articles_details';

        if (static::FEED_TYPE === PropertyExport::FEED_TYPE && $this->isDeltaExport()) {
            $sql = 'SELECT COUNT(DISTINCT s_articles_details_id) as count FROM 8s_articles_details_change_queue';
        }

        $count = Shopware()->Db()->query($sql)->fetchColumn();
        return intval($count);
    }

    /**
     * @param $numArticles
     * @param $currentArticle
     * @throws \Zend_Db_Adapter_Exception
     */
    private function updateStatus($numArticles, $currentArticle)
    {
        $progress = floor($currentArticle / $numArticles * 100);

        if ($progress !== $this->currentProgress) {
            RunCronOnce::updateProgress(static::CRON_NAME, $progress);
            $this->currentProgress = $progress;
        }
    }

    /**
    * @throws \Zend_Db_Adapter_Exception
    * @throws \Zend_Db_Statement_Exception
    */
    private function emptyQueue()
    {
        if ($this->isDeltaExport() === false || static::FEED_TYPE !== PropertyExport::FEED_TYPE) {
            return;
        }

        $sql = 'DELETE FROM 8s_articles_details_change_queue';
        Shopware()->Db()->query($sql);
    }

    protected function isDeltaExport() {
        return Config::getOption(Config::OPTION_EXPORT_TYPE) === Config::OPTION_EXPORT_TYPE_VALUE_DELTA;
    }
}
