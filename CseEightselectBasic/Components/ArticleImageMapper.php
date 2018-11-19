<?php
namespace CseEightselectBasic\Components;

class ArticleImageMapper
{
  /**
  * @param  int $detailId
  * @param  int $articleId
  * @return array
  */
  public function getImagePathsByVariant($detailId, $articleId)
  {
    $sql = "SELECT CONCAT('media/image/', ai.img, '.', ai.extension) as path FROM s_articles_img ai
    LEFT JOIN s_article_img_mappings aim on aim.image_id = ai.id
           LEFT JOIN s_article_img_mapping_rules aimr on aimr.mapping_id = aim.id
           LEFT JOIN s_article_configurator_option_relations acor ON acor.option_id = aimr.option_id AND acor.article_id = ?
           WHERE ai.articleID = ? AND (acor.id IS NOT NULL OR aimr.id IS NULL)
           ORDER BY acor.id DESC, ai.position ASC;";

    return Shopware()->Db()->query($sql, [$detailId, $articleId])->fetchAll();
  }
}
