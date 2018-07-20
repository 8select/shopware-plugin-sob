<?php
namespace CseEightselectBasic\Components;

use League\Csv\Writer;
use CseEightselectBasic\Components\RunCronOnce;

class PropertyExport
{
    /**
     * @const string
     */
    const STORAGE = 'files/8select/';

    /**
     * @var array
     */
    public $fields = [
        'prop_sku' => 'sku',
        'prop_isInStock' => 'instock',
        'prop_parentSku' => 'mastersku',
        'prop_ean' => 'ean',
        'prop_model' => 'model',
        'prop_name' => 'name1',
        'prop_discountPrice' => 'streich_preis',
        'prop_retailPrice' => 'angebots_preis',
        'prop_size' => 'groesse',
        'prop_brand' => 'marke',
        'prop_color' => 'farbe',
        'prop_url' => 'produkt_url',
        'prop_description' => 'beschreibung1',
        'images' => 'bilder'
      ];

    const CRON_NAME = '8select_property_export';

    /**
     * @var int
     */
    private $currentProgress = null;

    public function scheduleCron()
    {
        RunCronOnce::runOnce(self::CRON_NAME);
    }

    /**
     * @throws \Exception
     */
    public function doCron()
    {
        try {
            if (!ConfigValidator::isConfigValid()) {
                $message = 'Property Export nicht ausgeführt, da die Plugin Konfiguration ungültig ist.';
                Shopware()->PluginLogger()->warning($message);

                if (getenv('ES_DEBUG')) {
                    echo $message;
                }

                return;
            }

            if (RunCronOnce::isRunning(self::CRON_NAME)) {
                $message = 'Property Export nicht ausgeführt, es läuft bereits ein Export.';
                Shopware()->PluginLogger()->info($message);
                if (getenv('ES_DEBUG')) {
                    echo $message . PHP_EOL;
                }
                return;
            }

            if (!RunCronOnce::isScheduled(self::CRON_NAME)) {
                $message = 'Property Export nicht ausgeführt, es ist kein Export in der Warteschleife.';
                Shopware()->PluginLogger()->info($message);
                if (getenv('ES_DEBUG')) {
                    echo $message . PHP_EOL;
                }
                return;
            }

            Shopware()->PluginLogger()->info('Führe Property Export aus.');
            if (getenv('ES_DEBUG')) {
                echo 'Führe Property Export aus.' . PHP_EOL;
            }

            RunCronOnce::runCron(self::CRON_NAME);

            if ($this->getNumArticles() <= 0) {
                RunCronOnce::finishCron(self::CRON_NAME);

                Shopware()->PluginLogger()->info('Property Export nicht ausgeführt, da keine geänderten Artikel vorhanden.');
                return;
            }

            $start = time();
            $config = Shopware()->Container()->get('shopware.plugin.cached_config_reader');
            $feedId = $config->getByPluginName('CseEightselectBasic')['8s_feed_id'];
            $feedType = 'property_feed';
            $timestampInMillis = round(microtime(true) * 1000);
            $filename = sprintf('%s_%s_%d.csv', $feedId, $feedType, $timestampInMillis);

            if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                require_once __DIR__ . '/../vendor/autoload.php';
            }

            $csvWriter = Writer::createFromPath(self::STORAGE . $filename, 'a');
            $csvWriter->setDelimiter(';');

            $csvWriter->insertOne(array_keys($this->fields));

            $this->writeFile($csvWriter);
            AWSUploader::upload($filename, self::STORAGE, $feedId, $feedType);

            $this->emptyQueue();

            FeedLogger::logFeed(self::CRON_NAME);
            RunCronOnce::finishCron(self::CRON_NAME);

            if (getenv('ES_DEBUG')) {
                echo('Property Export abgeschlossen in ' . (time() - $start) . "s\n");
            }
            Shopware()->PluginLogger()->info('Property Export abgeschlossen in ' . (time() - $start) . 's');
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
            $this->updateStatus($numArticles, $i);

            $articles = $this->getArticles($attributeMapping, $i, $batchSize);
            foreach ($articles as $article) {
                $line = FieldHelper::getLine($article, array_values($this->originalFields));
                $csvWriter->insertOne($line);
            }
        }
    }

    /**
     * @param $mapping
     * @param $from
     * @param $number
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Statement_Exception
     * @return array
     */
    protected function getArticles($mapping, $from, $number)
    {
        $sql = 'SELECT DISTINCT ' . $mapping . ',
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
                    INNER JOIN 8s_articles_details_change_queue ON 8s_articles_details_change_queue.s_articles_details_id = s_articles_details.id
                    INNER JOIN s_articles_supplier ON s_articles_supplier.id = s_articles.supplierID
                    INNER JOIN s_core_tax ON s_core_tax.id = s_articles.taxID
                LIMIT ' . $number . ' OFFSET ' . $from;

        if (getenv('ES_DEBUG')) {
            echo  \PHP_EOL . 'SQL'  . \PHP_EOL;
            echo $sql . \PHP_EOL;
        }

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
        $sql = 'SELECT COUNT(DISTINCT s_articles_details_id) as count FROM 8s_articles_details_change_queue';
        return intval(Shopware()->Db()->query($sql)->fetchColumn());
    }


    /**
     * @param $numArticles
     * @param $currentArticle
     * @throws \Zend_Db_Adapter_Exception
     */
    private function updateStatus($numArticles, $currentArticle)
    {
        $progress = floor($currentArticle / $numArticles * 100);

        if ($progress != $this->currentProgress) {
            RunCronOnce::updateProgress(self::CRON_NAME, $progress);
            $this->currentProgress = $progress;
        }
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     */
    protected function emptyQueue()
    {
        $sql = 'DELETE FROM 8s_articles_details_change_queue';
        Shopware()->Db()->query($sql);
    }
}
