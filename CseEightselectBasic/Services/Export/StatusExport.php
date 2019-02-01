<?php

namespace CseEightselectBasic\Services\Export;

use CseEightselectBasic\Services\Dependencies\Provider;

class StatusExport
{

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * @param Provider $container
     * @param \Enlight_Components_Db_Adapter_Pdo_Mysql $db
     */
    public function __construct(Provider $provider, \Enlight_Components_Db_Adapter_Pdo_Mysql $db)
    {
        $this->provider = $provider;
        $this->db = $db;
    }

    /**
     * @param int $limit
     * @param int $offset
     */
    public function getProducts($limit, $offset)
    {
        $sqlTemplate = 'SELECT
                    s_articles_details.ordernumber as prop_sku,
                    ROUND(
                        CAST(
                            s_articles_prices.price * (100 + s_core_tax.tax) / 100 AS DECIMAL(10,3)
                        ),
                        2
                    ) as prop_discountPrice,
                    ROUND(
                        CAST(
                            IF(
                                s_articles_prices.pseudoprice = 0,
                                s_articles_prices.price,
                                s_articles_prices.pseudoprice
                            ) * (100 + s_core_tax.tax) / 100 AS DECIMAL(10,3)
                        ),
                        2
                    ) as prop_retailPrice,
                    IF (
                        s_articles_details.active && (!s_articles.laststock || s_articles_details.instock > 0),
                        1,
                        0
                    ) as prop_isInStock
                FROM s_articles_details
                    INNER JOIN s_articles
                        ON s_articles.id = s_articles_details.articleID
                    INNER JOIN s_articles_prices
                        ON s_articles_prices.articledetailsID = s_articles_details.id
                        AND s_articles_prices.from = 1
                        AND s_articles_prices.pricegroup = "EK"
                    INNER JOIN s_core_tax
                        ON s_core_tax.id = s_articles.taxID
                    INNER JOIN (
                        SELECT articleID
                        FROM s_articles_categories_ro
                        WHERE categoryID = %s
                        GROUP BY articleID
                    ) categoryConstraint
                        ON categoryConstraint.articleID = s_articles_details.articleId
                LIMIT %d OFFSET %d';

        $activeShop = $this->provider->getShopWithActiveCSE();
        $sql = sprintf($sqlTemplate, $activeShop->getCategory()->getId(), $limit, $offset);

        $products = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        return $products;
    }
}
