<?php
namespace CseEightselectBasic\Components;

class ArticleImageMapper
{

  /**
  * @param  int $ordernumber
  * @throws \Exception
  * @return array
  */
  public function getVariantImageMediaIdsByOrdernumber( $ordernumber ) 
  {
    $mediaIds = array();
    $hasVariants = false;
    $sql = 'SELECT articleID, id FROM s_articles_details WHERE ordernumber = ?';
    $sArticle = Shopware()->Db()->query($sql, [$ordernumber])->fetch();

    $allArticleOptionIDs = self::getOptionIdsByDetailId( $sArticle['id'] );
    $allArticleImageIDs = self::getImageIdsByArticleId( $sArticle['articleID'] );

    foreach( $allArticleImageIDs as $imageID ) {
      foreach ( $allArticleOptionIDs as $optionID ) {
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

  /**
  * @param  int $detailID
  * @throws \Exception
  * @return array
  */
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

  /**
  * @param  int $articleID
  * @throws \Exception
  * @return array
  */
  private function getImageIdsByArticleId ( $articleID ) 
  {
    $sql = 'SELECT id FROM s_articles_img WHERE articleID = ' . $articleID . ';';
    $images = Shopware()->Db()->query($sql)->fetchAll();
    $imageIDs = array();

    foreach ($images as $imageData) {
      foreach($imageData as $id) {
        array_push($imageIDs, $id);
      }  
    }
    
    return $imageIDs;
  }

  /**
  * @param  int $imageID
  * @param  int $optionID
  * @throws \Exception
  * @return boolean
  */
  private function doesImageMatchVariantOption( $imageID, $optionID ) 
  {
    $mappingQuery = 'SELECT id FROM s_article_img_mappings WHERE image_id =?';
    $optionQuery = 'SELECT option_id FROM s_article_img_mapping_rules WHERE mapping_id = ?';
    $targetMappingId = Shopware()->Db()->fetchOne($mappingQuery, [$imageID]);
    $targetOptionId = Shopware()->Db()->fetchOne($optionQuery, [$targetMappingId]);

    if ($optionID === $targetOptionId) {
      return true;
    } else {
      return false;
    }
  }
}
