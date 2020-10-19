<?php

namespace CseEightselectBasic\Services\Export;

class StatusExportMapper
{
    public function mapStatusFieldsToRawDataFields($product)
    {
        $mapped = [];
        if(!is_null($product['prop_sku'])) {
            $mapped['s_articles_details.ordernumber'] = $product['prop_sku'];
        }
        if(!is_null($product['prop_discountPrice'])) {
            $mapped['s_articles_prices.price'] = $product['prop_discountPrice'];
        }
        if(!is_null($product['prop_retailPrice'])) {
            $mapped['s_articles_prices.pseudoprice'] = $product['prop_retailPrice'];
        }
        if(!is_null($product['prop_isInStock'])) {
            $mapped['s_articles_details.isInStock'] = $product['prop_isInStock'];
        }

        return $mapped;
    }
}