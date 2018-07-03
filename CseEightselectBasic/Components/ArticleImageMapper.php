<?php
namespace CseEightselectBasic\Components;

class ArticleImageMapper
{

  public function getVariantImageMediaIdsByOrdernumber( $ordernumber ) 
  {
    $mediaIds = array();
    $sql = 'SELECT articleID, id FROM s_articles_details WHERE ordernumber = ?';
    $sArticle = Shopware()->Db()->query($sql, [$ordernumber])->fetchAll();
    $sArticle = $sArticle[0];
    $hasVariants = false;

    $optionIDs = self::getOptionIdsByDetailId( $sArticle['id'] );
    $allArticleImageIDs = self::getImageIdsByArticleId( $sArticle['articleID'] );

    foreach( $allArticleImageIDs as $imageID ) {
      foreach ( $optionIDs as $optionID ) {
        if (self::doesImageMatchVariantOption($imageID, $optionID)) {
          $matchingMediaId = Shopware()->Db()->fetchOne('SELECT media_id FROM s_articles_img WHERE id = ?', [$imageID]);
          array_push($mediaIds, $matchingMediaId);

          $hasVariants = true;
        }
      }
    }

    if (!$hasVariants) {
      $imagesQuery = 'SELECT media_id FROM s_articles_img WHERE articleID = ?';
      $standardMediaIds = Shopware()->Db()->query($imagesQuery, [$sArticle['articleID']])->fetchAll();

      foreach ($standardMediaIds as $standardMedia) {
        foreach ($standardMedia as $mediaId) {
          array_push($mediaIds, $mediaId);
        }
      }
    }

    return $mediaIds;
  }

  private function getOptionIdsByDetailId( $detailID ) 
  {
    $sql = 'SELECT option_id FROM s_article_configurator_option_relations WHERE article_id = ' . $detailID . ';';
    $options = Shopware()->Db()->query($sql)->fetchAll();
    $optionIDs = array();

    foreach($options as $optionData) {
      foreach($optionData as $optionID) {
        array_push($optionIDs, $optionID);
      }
    }

    return $optionIDs;
  }

  private function getImageIdsByArticleId ( $articleId ) 
  {
    $sql = 'SELECT id FROM s_articles_img WHERE articleID = ' . $articleId . ';';
    $images = Shopware()->Db()->query($sql)->fetchAll();
    $imageIDs = array();

    foreach ($images as $imageData) {
      foreach($imageData as $id) {
        array_push($imageIDs, $id);
      }  
    }
    
    return $imageIDs;
  }

  private function doesImageMatchVariantOption( $imageID, $optionID ) 
  {
    $mappingQuery = 'SELECT id FROM s_article_img_mappings WHERE image_id =?';
    $targetMappingId = Shopware()->Db()->fetchOne($mappingQuery, [$imageID]);
    $optionQuery = 'SELECT option_id FROM s_article_img_mapping_rules WHERE mapping_id = ?';
    $targetOptionId = Shopware()->Db()->fetchOne($optionQuery, [$targetMappingId]);

    if ($optionID === $targetOptionId) {
      return true;
    } else {
      return false;
    }
  }
}
