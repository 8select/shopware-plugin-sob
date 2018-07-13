<?php
namespace CseEightselectBasic\Components;

class ArticleImageMapper
{
  /**
  * @param  int $ordernumber
  * @throws \Exception
  * @return array
  */
  public function getVariantImageMediaIdsByOrdernumber( $ordernumber, $articleVariantId, $articleId) 
  {
    $optionIds = self::getOptionIdsByArticleVariantId($articleVariantId);
    $articleImages = self::getImagesWithMapping($articleId);
    $variantImages = self::getVariantImages($articleImages, $optionIds);

    $images = !empty($variantImages) ? $variantImages : $articleImages;

    return self::extractMediaIds($images);
  }

  /**
  * @param array $images
  * @return array
  */
  private function extractMediaIds($images) {
    return array_map(function($image) { return $image['mediaId']; }, $images);
  }

  /**
  * @param array $images
  * @param array $optionIds
  * @return array
  */
  private function getVariantImages($images, $optionIds)
  {
    $variantImages = array_filter($images, function($image) use (&$optionIds) {
      return !empty(array_intersect($image['optionIds'], $optionIds));
    });

    return $variantImages;
  }

  /**
  * @param int $articleId
  * @return array
  */
  private function getOptionIdsByArticleVariantId($articleId)
  {
    $sql = 'SELECT option_id FROM s_article_configurator_option_relations WHERE article_id = ' . $articleId . ';';
    $options = Shopware()->Db()->query($sql)->fetchAll();

    $optionIds = array_map(function($option) { return $option['option_id']; }, $options);

    return $optionIds;
  }

  /**
  * @param int $articleId
  * @throws \Exception
  * @return array
  */
  private function getImagesWithMapping($articleId) {
    $mappingsSql = 'SELECT 
        ai.id imageId, 
        ai.media_id mediaId, 
        GROUP_CONCAT(aimr.option_id) optionIds 
        FROM s_articles_img ai
        LEFT JOIN s_article_img_mappings aim ON aim.image_id = ai.id
        LEFT JOIN s_article_img_mapping_rules aimr ON aimr.mapping_id = aim.id
        WHERE ai.articleID = ?
        GROUP BY ai.id;';

    $imageMappings = Shopware()->Db()->query($mappingsSql, [$articleId])->fetchAll();
    $images = array_map(function($image) {
      return array(
        imageId => $image['id'],
        mediaId => $image['mediaId'],
        optionIds => $image['optionIds'] == null ? [] : explode(',', $image['optionIds'])
      );
    }, $imageMappings);

    return $images;

  }
}
