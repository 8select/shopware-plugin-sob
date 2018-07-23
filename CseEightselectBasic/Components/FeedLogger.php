<?php
namespace CseEightselectBasic\Components;

class FeedLogger {

  const TABLE_NAME = '8s_feeds';

  /**
  * @throws \Zend_Db_Adapter_Exception
  * @throws \Zend_Db_Statement_Exception
  */

  public static function logFeed($feedName) {

    $sql = 'INSERT INTO `' . self::TABLE_NAME . '` (feed_name, last_run)
            VALUES ("' . $feedName . '", NOW())
            ON DUPLICATE KEY UPDATE
              last_run = NOW();';

    if (getenv('ES_DEBUG')) {
      echo  \PHP_EOL . 'SQL'  . \PHP_EOL;
      echo $sql . \PHP_EOL;
    }

    Shopware()->Db()->query($sql);
  }

  public static function getLastFeedUpdate($feedName) {
    return Shopware()->Db()->fetchOne('SELECT last_run FROM ' . self::TABLE_NAME . ' WHERE feed_name = "' . $feedName . '";');
  }

  public static function createTable() 
  {
    $sql = 'CREATE TABLE IF NOT EXISTS`' . self::TABLE_NAME . '` (
              `feed_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `last_run` datetime DEFAULT NULL,
              PRIMARY KEY (`feed_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';

    Shopware()->Db()->query($sql);
  }

  public static function deleteTable() {
    $sql = 'DROP TABLE IF EXISTS `' . self::TABLE_NAME . '`;';
    Shopware()->Db()->query($sql);
  }

  public static function deleteFeedEntryByName($feedName) {
    $sql = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE feed_name = "' . $feedName . '";';
    Shopware()->Db()->query($sql);
  }
}
