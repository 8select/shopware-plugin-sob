<?php

namespace CseEightselectBasic\tests\Unit\Services\Export\Helper\ProductPrice;

use CseEightselectBasic\Services\Export\Helper\ProductPrice;

class ProductPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductPrice
     */
    private $service;

    /**
     * @before
     */
    public function createServiceBefore()
    {
        $this->service = new ProductPrice();
    }

    public function test_it_can_be_created()
    {
        $service = new ProductPrice();
        $this->assertInstanceOf(ProductPrice::class, $service);
    }

    public function test_it_gets_gross_price()
    {
        $article = [
            'angebots_preis' => 20,
            'tax' => 19,
        ];
        $field = 'angebots_preis';

        $result = $this->service->getGrossPrice($article, $field);

        $this->assertEquals('23.80', $result);
    }
}
