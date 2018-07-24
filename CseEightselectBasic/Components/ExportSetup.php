<?php
namespace CseEightselectBasic\Components;

class ExportSetup
{
    public static function createChangeQueueTable() {
        $query = 'CREATE TABLE `8s_articles_details_change_queue` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `s_articles_details_id` int(11) NOT NULL,
            `updated_at` datetime,
            PRIMARY KEY (`id`)
          ) COLLATE=\'utf8_unicode_ci\' ENGINE=InnoDB DEFAULT CHARSET=utf8;';

        Shopware()->Db()->query($query);
    }

    public static function createChangeQueueTriggers() {
        $sArticlesTrigger = 'CREATE TRIGGER `8s_articles_change_queue_writer`
            AFTER UPDATE on `s_articles`
                FOR EACH ROW
                BEGIN
                    INSERT INTO 8s_articles_details_change_queue (s_articles_details_id, updated_at)
                    SELECT
                        id as s_articles_details_id,
                        NOW() as updated_at
                    FROM s_articles_details
                    WHERE articleID = NEW.id;
                END';

        $sArticlesDetailsTrigger = 'CREATE TRIGGER `8s_articles_details_change_queue_writer`
            AFTER UPDATE on `s_articles_details`
                FOR EACH ROW
                BEGIN
                    INSERT INTO 8s_articles_details_change_queue (s_articles_details_id, updated_at)
                    VALUES (NEW.id, NOW());
                END';

        $sArticlesImgTrigger = 'CREATE TRIGGER `8s_articles_img_change_queue_writer`
            AFTER UPDATE on `s_articles_img`
                FOR EACH ROW
                BEGIN
                    INSERT INTO 8s_articles_details_change_queue (s_articles_details_id, updated_at)
                    SELECT
                        id as s_articles_details_id,
                        NOW() as updated_at
                    FROM s_articles_details
                    WHERE articleID = NEW.articleID;
                END';

        $sArticlesPricesTrigger = 'CREATE TRIGGER `8s_s_articles_prices_change_queue_writer`
            AFTER UPDATE on `s_articles_prices`
                  FOR EACH ROW
                  BEGIN
                    IF (NEW.price != OLD.price
                    OR NEW.pseudoprice != OLD.pseudoprice)
                    THEN
                        INSERT INTO 8s_articles_details_change_queue (s_articles_details_id, updated_at)
                        VALUES (NEW.articleDetailsID, NOW());
                    END IF;
                  END';

        $sArticlesAttributesTrigger = 'CREATE TRIGGER `8s_s_articles_attributes_change_queue_writer`
            AFTER UPDATE on `s_articles_attributes`
                FOR EACH ROW
                BEGIN
                    INSERT INTO 8s_articles_details_change_queue (s_articles_details_id, updated_at)
                    VALUES (NEW.articleDetailsID, NOW());
                END';

        $sArticleConfiguratorOptionRelationsTrigger = 'CREATE TRIGGER `8s_s_article_configurator_option_relations_change_queue_writer`
            AFTER UPDATE on `s_article_configurator_option_relations`
                  FOR EACH ROW
                  BEGIN
                      INSERT INTO 8s_articles_details_change_queue (s_articles_details_id, updated_at)
                      VALUES (NEW.article_id, NOW());
                  END';

        $sArticleImgMappingsTrigger = 'CREATE TRIGGER `8s_s_article_img_mappings_change_queue_writer`
            AFTER UPDATE on `s_article_img_mappings`
                FOR EACH ROW
                BEGIN
                    INSERT INTO 8s_articles_details_change_queue (s_articles_details_id, updated_at)
                        SELECT
                            ad.id as s_articles_details_id,
                            NOW() as updated_at
                        FROM s_articles_img ai
                        LEFT JOIN s_articles_details ad ON ad.articleID = ai.articleID
                        WHERE ai.id = NEW.image_id;
                END';

        $sArticleImgMappingRulesTrigger = 'CREATE TRIGGER `8s_s_article_img_mapping_rules_change_queue_writer`
            AFTER UPDATE on `s_article_img_mapping_rules`
                  FOR EACH ROW
                  BEGIN
                    INSERT INTO 8s_articles_details_change_queue (s_articles_details_id, updated_at)
                        SELECT
                            ad.id as s_articles_details_id,
                            NOW() as updated_at
						FROM s_article_img_mappings aim
                        INNER JOIN s_articles_img ai on ai.id = aim.image_id
						INNER JOIN s_articles_details ad ON ad.articleID = ai.articleID
                        WHERE aim.id = NEW.mapping_id;
                  END';

        $triggerQueries = [
            $sArticlesTrigger,
            $sArticlesDetailsTrigger,
            $sArticlesImgTrigger,
            $sArticlesPricesTrigger,
            $sArticlesAttributesTrigger,
            $sArticleConfiguratorOptionRelationsTrigger,
            $sArticleImgMappingsTrigger,
            $sArticleImgMappingRulesTrigger
        ];

        foreach ($triggerQueries as $query) {
            Shopware()->Db()->query($query);
        }
    }

    public static function dropChangeQueueTable() {
        Shopware()->Db()->query('DROP TABLE IF EXISTS `8s_articles_details_change_queue`');
    }

    public static function dropChangeQueueTriggers() {
        $triggerQueries = [
            'DROP TRIGGER IF EXISTS `8s_articles_change_queue_writer`',
            'DROP TRIGGER IF EXISTS `8s_articles_details_change_queue_writer`',
            'DROP TRIGGER IF EXISTS `8s_articles_img_change_queue_writer`',
            'DROP TRIGGER IF EXISTS `8s_s_articles_prices_change_queue_writer`',
            'DROP TRIGGER IF EXISTS `8s_s_articles_attributes_change_queue_writer`',
            'DROP TRIGGER IF EXISTS `8s_s_article_configurator_option_relations_change_queue_writer`',
            'DROP TRIGGER IF EXISTS `8s_s_article_img_mappings_change_queue_writer`',
            'DROP TRIGGER IF EXISTS `8s_s_article_img_mapping_rules_change_queue_writer`',
        ];

        foreach ($triggerQueries as $query) {
            Shopware()->Db()->query($query);
        }
    }
}
