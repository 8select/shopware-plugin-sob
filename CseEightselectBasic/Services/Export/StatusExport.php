<?php

namespace CseEightselectBasic\Services\Export;

use CseEightselectBasic\Services\Dependencies\Provider;
use CseEightselectBasic\Services\Export\ExportInterface;
use CseEightselectBasic\Services\Export\StatusExportDelta;
use Doctrine\DBAL\Connection;

class StatusExport implements ExportInterface
{

    /**
     * @var Provider
     */
    private $provider;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Provider $container
     * @param Connection $connection
     */
    public function __construct(Provider $provider, Connection $connection)
    {
        $this->provider = $provider;
        $this->connection = $connection;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param bool $isDeltaExport = true
     * @return array
     */
    public function getProducts($limit, $offset, $isDeltaExport = true)
    {
        $productsSql = implode(
            ' ',
            [
                $this->getSelectQueryString(false),
                $this->getFromQueryString(),
                $this->getDeltaConditionQueryString($isDeltaExport),
                $this->getLimitQueryString($limit, $offset),
                ';',
            ]
        );
        $activeShop = $this->provider->getShopWithActiveCSE();
        $params = [
            'categoryId' => $activeShop->getCategory()->getId(),
        ];

        $products = $this->connection->fetchAll($productsSql, $params);

        $deltaSql = implode(
            ' ',
            [
                $this->getSelectQueryString(true),
                $this->getFromQueryString(),
                $this->getDeltaConditionQueryString($isDeltaExport),
                $this->getLimitQueryString($limit, $offset),
                ';',
            ]
        );
        $deltaUpdate = new StatusExportDelta($this->connection);
        $deltaUpdate->writeDeltaStatus($deltaSql, $params);

        return $products;
    }

    /**
     * @param bool $isDeltaExport
     * @return int
     */
    public function getTotal($isDeltaExport = true)
    {
        $sql = implode(
            ' ',
            [
                $this->getCountQueryString(),
                $this->getFromQueryString(),
                $this->getDeltaConditionQueryString($isDeltaExport),
                ';',
            ]
        );

        $activeShop = $this->provider->getShopWithActiveCSE();
        $params = [
            'categoryId' => $activeShop->getCategory()->getId(),
        ];

        $count = $this->connection->fetchColumn($sql, $params);

        return intval($count);
    }

    /**
     * @return string
     */
    private function getCountQueryString()
    {
        return 'SELECT COUNT(s_articles_details.id)';
    }

    /**
     * @return string
     */
    private function getSelectQueryString($withArticleDetailId = false)
    {
        $template = 'SELECT
                    %s
                    s_articles_details.ordernumber as prop_sku,
                    ROUND(
                        CAST(
                            IFNULL(priceGroupPrice.price, defaultPrice.price) * (100 + s_core_tax.tax) / 100 AS DECIMAL(10,3)
                        ),
                        2
                    ) as prop_discountPrice,
                    ROUND(
                        CAST(
                            IF(
                                IFNULL(priceGroupPrice.pseudoprice, defaultPrice.pseudoprice) = 0,
                                IFNULL(priceGroupPrice.price, defaultPrice.price),
                                IFNULL(priceGroupPrice.pseudoprice, defaultPrice.pseudoprice)
                            ) * (100 + s_core_tax.tax) / 100 AS DECIMAL(10,3)
                        ),
                        2
                    ) as prop_retailPrice,
                    IF (
                        s_articles.active &&
                        s_articles_details.active &&
                        (!s_articles.laststock || s_articles_details.instock > 0),
                        1,
                        0
                    ) as prop_isInStock';

        $detailSelect = '';

        if ($withArticleDetailId) {
            $detailSelect = 's_articles_details.id as s_articles_details_id,';
        }

        return sprintf($template, $detailSelect);
    }

    /**
     * @return string
     */
    private function getFromQueryString()
    {
        $sqlTemplate = 'FROM s_articles_details
                    INNER JOIN s_articles
                        ON s_articles.id = s_articles_details.articleID
                    INNER JOIN s_core_tax
                        ON s_core_tax.id = s_articles.taxID
                    INNER JOIN (
                        SELECT articleID
                        FROM s_articles_categories_ro
                        WHERE categoryID = :categoryId
                        GROUP BY articleID
                    ) categoryConstraint
                        ON categoryConstraint.articleID = s_articles_details.articleID
                    LEFT JOIN s_articles_prices as priceGroupPrice
                        ON priceGroupPrice.articledetailsID = s_articles_details.id
                        AND priceGroupPrice.from = 1
                        AND priceGroupPrice.pricegroup = "%s"
                    LEFT JOIN s_articles_prices as defaultPrice
                        ON defaultPrice.articledetailsID = s_articles_details.id
                        AND defaultPrice.from = 1
                        AND defaultPrice.pricegroup = "EK"
                        ';

        $activeShop = $this->provider->getShopWithActiveCSE();
        $customerGroup = $activeShop->getCustomerGroup();
        $customerGroupKey = $customerGroup->getKey();

        return sprintf($sqlTemplate, $customerGroupKey);
    }

    /**
     * @return string
     */
    private function getDeltaConditionQueryString($isDeltaExport)
    {
        if (!$isDeltaExport) {
            return '';
        }

        return 'LEFT JOIN ' . StatusExportDelta::TABLE_NAME . ' delta
                ON
                    delta.s_articles_details_id = s_articles_details.id
                    AND
                        delta.prop_isInStock = IF (
                            s_articles.active &&
                            s_articles_details.active &&
                            (!s_articles.laststock || s_articles_details.instock > 0),
                            1,
                            0
                        )
                    AND
                        delta.prop_discountPrice =
                            ROUND(
                                CAST(
                                    IFNULL(priceGroupPrice.price, defaultPrice.price) * (100 + s_core_tax.tax) / 100 AS DECIMAL(10,3)
                                ),
                                2
                            )
                    AND
                        delta.prop_retailPrice =
                            ROUND(
                                CAST(
                                    IF(
                                        IFNULL(priceGroupPrice.pseudoprice, defaultPrice.pseudoprice) = 0,
                                        IFNULL(priceGroupPrice.price, defaultPrice.price),
                                        IFNULL(priceGroupPrice.pseudoprice, defaultPrice.pseudoprice)
                                    ) * (100 + s_core_tax.tax) / 100 AS DECIMAL(10,3)
                                ),
                                2
                            )
                WHERE delta.s_articles_details_id IS NULL';
    }

    /**
     * @return string
     */
    private function getLimitQueryString($limit, $offset)
    {
        return sprintf('LIMIT %d OFFSET %d', $limit, $offset);
    }
}
