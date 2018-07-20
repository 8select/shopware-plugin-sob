<?php
namespace CseEightselectBasic\Components;

use League\Csv\Writer;
use CseEightselectBasic\Components\RunCronOnce;
use CseEightselectBasic\Components\FeedLogger;
use CseEightselectBasic\Components\ConfigValidator;

class ArticleExport
{
    const STORAGE = 'files/8select/';

    const CRON_NAME = '8select_article_export';

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

    public function scheduleCron()
    {
        RunCronOnce::runOnce(self::CRON_NAME);
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
            if (!ConfigValidator::isConfigValid()) {
                $message = 'Artikel Export nicht ausgeführt, da die Plugin Konfiguration ungültig ist.';
                Shopware()->PluginLogger()->warning($message);

                if (getenv('ES_DEBUG')) {
                    echo $message;
                }

                return;
            }

            if (RunCronOnce::isRunning(self::CRON_NAME)) {
                $message = 'Artikel Export nicht ausgeführt, es läuft bereits ein Export.';
                Shopware()->PluginLogger()->info($message);
                if (getenv('ES_DEBUG')) {
                    echo $message . PHP_EOL;
                }
                return;
            }

            if (!RunCronOnce::isScheduled(self::CRON_NAME)) {
                $message = 'Artikel Export nicht ausgeführt, es ist kein Export in der Warteschleife.';
                Shopware()->PluginLogger()->info($message);
                if (getenv('ES_DEBUG')) {
                    echo $message . PHP_EOL;
                }
                return;
            }

            Shopware()->PluginLogger()->info('Führe Artikel Export aus.');
            if (getenv('ES_DEBUG')) {
                echo 'Führe Artikel Export aus.' . PHP_EOL;
            }

            RunCronOnce::runCron(self::CRON_NAME);

            $start = time();
            $config = Shopware()->Container()->get('shopware.plugin.cached_config_reader');
            $feedId = $config->getByPluginName('CseEightselectBasic')['8s_feed_id'];
            $feedType = 'product_feed';
            $timestampInMillis = round(microtime(true) * 1000);
            $filename = sprintf('%s_%s_%d.csv', $feedId, $feedType, $timestampInMillis);

            if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                require_once __DIR__ . '/../vendor/autoload.php';
            }

            $csvWriter = Writer::createFromPath(self::STORAGE . $filename, 'a');
            $csvWriter->setDelimiter(';');

            // insert header
            $csvWriter->insertOne($this->fields);

            $this->writeFile($csvWriter, $queueId);

            AWSUploader::upload($filename, self::STORAGE, $feedId, $feedType);

            FeedLogger::logFeed(self::CRON_NAME);
            RunCronOnce::finishCron(self::CRON_NAME);

            if (getenv('ES_DEBUG')) {
                echo('Artikel Export abgeschlossen in ' . (time() - $start) . "s\n");
            }
            Shopware()->PluginLogger()->info('Artikel Export abgeschlossen in ' . (time() - $start) . 's');
        } catch (\Exception $exception) {
            Shopware()->PluginLogger()->error($exception);
            RunCronOnce::finishCron(self::CRON_NAME);
            throw $exception;
        }
    }

    /**
     * @param Writer $csvWriter
     * @throws \Doctrine\ORM\ORMException
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @throws \League\Csv\CannotInsertRecord
     */
    protected function writeFile(Writer $csvWriter)
    {
        $attributeMappingQuery = 'SELECT GROUP_CONCAT(CONCAT(shopwareAttribute," AS ",eightselectAttribute)) as resultMapping
                         FROM 8s_attribute_mapping
                         WHERE shopwareAttribute != "-"
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
     * @param int $number
     * @param int $from
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return array
     */
    protected function getArticles($mapping, $from, $number)
    {
        $sql = 'SELECT ' . $mapping . ',
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
                INNER JOIN s_articles_details AS ad2 ON ad2.articleID = s_articles_details.articleID AND ad2.kind = 1
                INNER JOIN s_articles ON s_articles.id = s_articles_details.articleID
                INNER JOIN s_articles_attributes ON s_articles_attributes.articledetailsID = s_articles_details.id
                INNER JOIN s_articles_prices ON s_articles_prices.articledetailsID = s_articles_details.id AND s_articles_prices.from = 1
                INNER JOIN s_articles_supplier ON s_articles_supplier.id = s_articles.supplierID
                INNER JOIN s_core_tax ON s_core_tax.id = s_articles.taxID
                LIMIT ' . $number . ' OFFSET ' . $from;

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
     * @return mixed
     */
    protected function getNumArticles()
    {
        $sql = 'SELECT id FROM s_articles_details';
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

        if ($progress !== $this->currentProgress) {
            RunCronOnce::updateProgress(self::CRON_NAME, $progress);
            $this->currentProgress = $progress;
        }
    }
}
