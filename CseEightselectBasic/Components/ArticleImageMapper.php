<?php
namespace CseEightselectBasic\Components;

class ArticleImageMapper
{
  /**
  * @param  string $ordernumber
  * @param  int $detailId
  * @param  int $articleId
  * @return array
  */
  public function getImagesByVariant( $ordernumber, $detailId, $articleId)
  {
    $variantImages = self::getVariantImages($detailId, $articleId);
    $parentImages = self::getParentImages($detailId, $articleId);

    if (!empty($variantImages)) {
      return $variantImages;
    }

    return $parentImages;
  }

  /**
  * @param  int $detailId
  * @param  int $articleId
  * @throws \Exception
  * @return array
  */
  private function getVariantImages($detailId, $articleId) {
    $variantImagesQuery = 'SELECT ai.img, ai.extension FROM s_articles_img ai
        LEFT JOIN s_article_img_mappings aim on aim.image_id = ai.id
        LEFT JOIN s_article_img_mapping_rules aimr on aimr.mapping_id = aim.id
        RIGHT JOIN s_article_configurator_option_relations acor ON acor.option_id = aimr.option_id AND acor.article_id = ' . $detailId . '
        WHERE ai.articleID = ' . $articleId . ';';
    $variantImagesRaw = Shopware()->Db()->query($variantImagesQuery)->fetchAll();

    return array_map(function($image) {
      return array(
        img => $image['img'],
        extension => $image['extension']
      );
    }, $variantImagesRaw);
  }
  
  /**
  * @param  int $detailId
  * @param  int $articleId
  * @throws \Exception
  * @return array
  */
  private function getParentImages($detailId, $articleId) {
    $parentImagesQuery = 'SELECT ai.img, ai.extension FROM s_articles_img ai WHERE ai.articleID = ' . $articleId . ';';
    $parentImagesRaw = Shopware()->Db()->query($parentImagesQuery)->fetchAll();

    return array_map(function($image) {
      return array(
        img => $image['img'],
        extension => $image['extension']
      );
    }, $parentImagesRaw);
  }
}
