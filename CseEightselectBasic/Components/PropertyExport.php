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
      'prop_sku',
      'prop_isInStock',
      'prop_parentSku',
      'prop_ean',
      'prop_model',
      'prop_name',
      'prop_discountPrice',
      'prop_retailPrice',
      'prop_size',
      'prop_brand',
      'prop_color',
      'prop_url',
      'prop_description',
      'images'
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
            Shopware()->PluginLogger()->info('Führe Property Export aus.');
            if (getenv('ES_DEBUG')) {
                echo 'Führe Property Export aus.' . PHP_EOL;
            }

            if (RunCronOnce::isRunning(self::CRON_NAME)) {
                if (getenv('ES_DEBUG')) {
                    echo 'Property Export nicht ausgeführt, es läuft bereits ein Property Export.' . PHP_EOL;
                }
                return;
            }

            if (!RunCronOnce::isScheduled(self::CRON_NAME)) {
                if (getenv('ES_DEBUG')) {
                    echo 'Property Export nicht ausgeführt, es ist kein Property Export in der Warteschleife.' . PHP_EOL;
                }
                return;
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

            $header = [];
            foreach ($this->fields as $field) {
                $header[] = $field;
            }
            $csvWriter->insertOne($header);

            $this->writeFile($csvWriter);
            AWSUploader::upload($filename, self::STORAGE, $feedId, $feedType);

            $this->emptyQueue();

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

    public static function getOriginalFieldNames($fields) {
        return array_map(function ($field) {
            switch($field) {
                case 'prop_sku':
                    return 'sku';
                    break;
                case 'prop_isInStock':
                    return 'instock';
                    break;
                case 'prop_parentSku':
                    return 'mastersku';
                    break;
                case 'prop_ean':
                    return 'ean';
                    break;
                case 'prop_model':
                    return 'model';
                    break;
                case 'prop_name':
                    return 'name1';
                    break;
                case 'prop_discountPrice':
                    return 'streich_preis';
                    break;
                case 'prop_retailPrice':
                    return 'angebots_preis';
                    break;
                case 'prop_size':
                    return 'groesse';
                    break;
                case 'prop_brand':
                    return 'marke';
                    break;
                case 'prop_color':
                    return 'farbe';
                    break;
                case 'prop_url':
                    return 'produkt_url';
                    break;
                case 'prop_description':
                    return 'beschreibung1';
                    break;
                case 'images':
                    return 'bilder';
                    break;
                default:
                    return $field;
            }
        }, $fields);
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

            $origFields = $this->getOriginalFieldNames($this->fields);
            $articles = $this->getArticles($attributeMapping, $i, $batchSize);

            foreach ($articles as $article) {
                $line = FieldHelper::getLine($article, $origFields);
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
