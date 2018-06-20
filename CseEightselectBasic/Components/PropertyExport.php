<?php
namespace CseEightselectBasic\Components;

use League\Csv\Writer;
use CseEightselectBasic\Components\RunCronOnce;

class PropertyExport {

  /**
   * @const string
   */
  const STORAGE = 'files/8select/';
  const CRON_NAME = '8select_property_export';
  
  /**
   * @var int
   */
  private $currentProgress = null;

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
    'prop_flags',
    'prop_url',
    'prop_description',
    'images'
  ];
  
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
      Shopware()->PluginLogger()->info('Führe Artikel Property Export aus.');
      if(getenv('ES_DEBUG')) {
        echo 'Führe Artikel Property Export aus.' . PHP_EOL;
      }

      if (RunCronOnce::isRunning(self::CRON_NAME)) {
        if (getenv('ES_DEBUG')) {
          echo 'Artikel Property Export nicht ausgeführt, es läuft bereits ein Artikel Property Export.' . PHP_EOL;
        }
        return;
      }

      if (!RunCronOnce::isScheduled(self::CRON_NAME)) {
        if (getenv('ES_DEBUG')) {
            echo 'Artikel Property Export nicht ausgeführt, es ist kein Artikel Property Export in der Warteschleife.' . PHP_EOL;
        }
        return;
      }

      RunCronOnce::runCron(self::CRON_NAME);

      if ($this->getNumArticles() <= 0) {
        RunCronOnce::finishCron(self::CRON_NAME);

        Shopware()->PluginLogger()->info('Artikel Property Export nicht ausgeführt, da keine geänderten Artikel vorhanden.');
        return;
      }

      $start = time();
      $config = Shopware()->Container()->get('shopware.plugin.cached_config_reader');
      $feedId = $config->getPluginByName('CseEightselectBasic')['8s_feed_id'];
      $feedType = 'property_feed';
      $timestampInMillis = round(microtime(true) * 1000);
      $filename = sprintf('%s_%s_%d.csv', $feedId, $feedType, $timestampInMillis);

      if(file_exists(__DIR__ . '/../vendor/autoload.php')) {
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

      if(getenv('ES_DEBUG')) {
        echo('Artikel Property Export abgeschlossen in ' . (time() - $start) . "s\n");
      }
      Shopware()->PluginLogger()->info('Artikel Property Export abgeschlossen in ' . (time() - $start) . 's');
    } catch(\Exception $exception) {
      Shopware()->PluginLogger()->error($exception);
      RunCronOnce::finishCron(self::CRON_NAME);
      throw $excepction;
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
              s_articles_prices.price AS prop_retailPrice,
              s_articles_prices.pseudoprice AS prop_discountPrice,
              s_articles_details.id AS detailID,
              s_articles_details.active AS prop_isInStock,
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
