<?php
namespace CseEightselectBasic\Components;

use CseEightselectBasic\Components\AWSUploader;
use CseEightselectBasic\Components\FeedLogger;
use CseEightselectBasic\Components\RunCronOnce;
use CseEightselectBasic\Services\Config\Config;
use CseEightselectBasic\Services\Config\Validator;
use CseEightselectBasic\Services\Dependencies\Provider;
use League\Csv\Writer;

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

    /**
     * @var Provider
     */
    protected $provider;

    /**
     * @var Validator
     */
    protected $configValidator;

    /**
     * @var Config
     */
    protected $config;

    public function __construct()
    {
        $container = Shopware()->Container();
        $this->provider = $container->get('cse_eightselect_basic.dependencies.provider');
        $this->configValidator = $container->get('cse_eightselect_basic.config.validator');
        $this->config = $container->get('cse_eightselect_basic.config.config');
        $this->mapper = $container->get('cse_eightselect_basic.export.helper.mapper');
    }

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
                // we need to remove products from shops that are not active for cse because those products are still logged here
                $this->emptyQueue();
                RunCronOnce::finishCron(static::CRON_NAME);
                return;
            }

            $message = sprintf('Führe %s aus.', static::CRON_NAME);
            Shopware()->PluginLogger()->info($message);

            RunCronOnce::runCron(static::CRON_NAME);
            $this->generateExportCSV();
            $this->emptyQueue();
            FeedLogger::logFeed(static::CRON_NAME);
            RunCronOnce::finishCron(static::CRON_NAME);

            $message = sprintf('%s abgeschlossen in %d s', static::CRON_NAME, (time() - $start));
            Shopware()->PluginLogger()->info($message);
        } catch (\Exception $exception) {
            Shopware()->PluginLogger()->error($exception);
            RunCronOnce::finishCron(static::CRON_NAME);

            throw $exception;
        }
    }

    /**
     * @return array
     */
    protected function canRunCron()
    {
        if (!RunCronOnce::isScheduled(static::CRON_NAME)) {
            $message = sprintf('%s nicht ausgeführt, es ist kein Export in der Warteschleife.', static::CRON_NAME);
            return false;
        }

        $validationResult = $this->configValidator->validateExportConfig();
        if ($validationResult['isValid'] === false) {
            $message = sprintf('%s nicht ausgeführt, da die Plugin Konfiguration ungültig ist.', static::CRON_NAME);
            Shopware()->PluginLogger()->warning($message);

            return false;
        }

        if ($this->getNumArticles() <= 0) {
            $message = sprintf('%s nicht ausgeführt, es wurden keine Produkte für Export gefunden.', static::CRON_NAME);

            return false;
        }

        if (RunCronOnce::isRunning(static::CRON_NAME)) {
            $message = sprintf('%s nicht ausgeführt, es läuft bereits ein Export.', static::CRON_NAME);
            return false;
        }

        return true;
    }

    private function generateExportCSV()
    {
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            require_once __DIR__ . '/../vendor/autoload.php';
        }

        try {
            $path = $this->createTempFile();
        } catch (\Exception $exception) {
            Shopware()->PluginLogger()->error($exception->getMessage());
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
                throw new \Exception($message, 500, $exception);
            }
            $path = tempnam($storagePath, static::FEED_TYPE);
        }

        $tempPath = new \SplFileInfo($path);
        if (!$tempPath->isWritable()) {
            $message = sprintf('%s nicht ausgeführt, temporäre Datei in Fallback Verzeichnis für Export konnte nicht erstellt werden.', static::CRON_NAME);
            throw new \Exception($message);
        }
        return $path;
    }

    /**
     * @return string
     */
    private function getAttributeMapping()
    {
        $attributeMappingQuery = 'SELECT GROUP_CONCAT(CONCAT(shopwareAttribute," AS ",eightselectAttribute)) as resultMapping
        FROM 8s_attribute_mapping
        WHERE shopwareAttribute != "-"
        AND shopwareAttribute != ""
        AND shopwareAttribute IS NOT NULL
        AND shopwareAttribute NOT LIKE "%id=%"';

        return Shopware()->Db()->query($attributeMappingQuery)->fetch(\PDO::FETCH_ASSOC)['resultMapping'];
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
        $numArticles = $this->getNumArticles();
        $batchSize = 100;

        $attributeMapping = $this->getAttributeMapping();
        for ($offset = 0; $offset < $numArticles; $offset += $batchSize) {
            $articles = $this->getArticles($attributeMapping, $i, $batchSize);

            $top = $offset + ($batchSize - 1);
            if ($top > $numArticles) {
                $top = $numArticles;
            }

            foreach ($articles as $article) {
                $line = $this->mapper->getLine($article, $this->fields);
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
        $sqlTemplate = "
            SELECT %s,
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
                INNER JOIN s_articles_prices ON s_articles_prices.articledetailsID = s_articles_details.id AND s_articles_prices.from = 1 AND s_articles_prices.pricegroup = 'EK'
                INNER JOIN s_articles_supplier ON s_articles_supplier.id = s_articles.supplierID
                INNER JOIN s_core_tax ON s_core_tax.id = s_articles.taxID
                INNER JOIN (
                    SELECT articleID
                    FROM s_articles_categories_ro
                    WHERE categoryID = %s
                    GROUP BY articleID
                ) categoryConstraint ON categoryConstraint.articleID = s_articles_details.articleId
            LIMIT %d OFFSET %d;
            ";

        $join = '';

        if (static::FEED_TYPE === PropertyExport::FEED_TYPE && $this->isDeltaExport()) {
            $join = ' INNER JOIN 8s_articles_details_change_queue ON 8s_articles_details_change_queue.s_articles_details_id = s_articles_details.id ';
        }

        $activeShop = $this->provider->getShopWithActiveCSE();
        $sql = sprintf($sqlTemplate, $mapping, $join, $activeShop->getCategory()->getId(), $limit, $offset);

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
        $sqlTemplate = "
            SELECT COUNT(s_articles_details.id)
            FROM s_articles_details
                %s
                INNER JOIN s_articles_details AS ad2 ON ad2.articleID = s_articles_details.articleID AND ad2.kind = 1
                INNER JOIN s_articles ON s_articles.id = s_articles_details.articleID
                INNER JOIN s_articles_attributes ON s_articles_attributes.articledetailsID = s_articles_details.id
                INNER JOIN s_articles_prices ON s_articles_prices.articledetailsID = s_articles_details.id AND s_articles_prices.from = 1 AND s_articles_prices.pricegroup = 'EK'
                INNER JOIN s_articles_supplier ON s_articles_supplier.id = s_articles.supplierID
                INNER JOIN s_core_tax ON s_core_tax.id = s_articles.taxID
                INNER JOIN (
                    SELECT articleID
                    FROM s_articles_categories_ro
                    WHERE categoryID = %s
                    GROUP BY articleID
                ) categoryConstraint ON categoryConstraint.articleID = s_articles_details.articleId;";

        $join = '';

        if (static::FEED_TYPE === PropertyExport::FEED_TYPE && $this->isDeltaExport()) {
            $join = ' INNER JOIN 8s_articles_details_change_queue ON 8s_articles_details_change_queue.s_articles_details_id = s_articles_details.id ';
        }

        $activeShop = $this->provider->getShopWithActiveCSE();
        $sql = sprintf($sqlTemplate, $join, $activeShop->getCategory()->getId(), $limit, $offset);

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

    protected function isDeltaExport()
    {
        return $this->config->getOption(Config::OPTION_EXPORT_TYPE) === Config::OPTION_EXPORT_TYPE_VALUE_DELTA;
    }
}
