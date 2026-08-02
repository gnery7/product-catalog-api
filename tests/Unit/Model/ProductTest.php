<?php

namespace ContatoSeguro\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Contatoseguro\TesteBackend\Model\Product;

class ProductTest extends TestCase
{
  public function testConstructor(): void
  {
    $product = new Product(1, 2, 'Test Product', 99.99, true, '2023-10-01', 5);

    $this->assertEquals(1, $product->id);
    $this->assertEquals(2, $product->companyId);
    $this->assertEquals('Test Product', $product->title);
    $this->assertEquals(99.99, $product->price);
    $this->assertTrue($product->active);
    $this->assertEquals('2023-10-01', $product->createdAt);
    $this->assertEquals(5, $product->stock);
  }


  public function testHydrateByFetch(): void
  {
    $fetch = new \stdClass();
    $fetch->id = 1;
    $fetch->company_id = 2;
    $fetch->title = 'Test Product';
    $fetch->price = 99.99;
    $fetch->active = true;
    $fetch->created_at = '2023-10-01';
    $fetch->stock = 5;

    $product = Product::hydrateByFetch($fetch);

    $this->assertEquals(1, $product->id);
    $this->assertEquals(2, $product->companyId);
    $this->assertEquals('Test Product', $product->title);
    $this->assertEquals(99.99, $product->price);
    $this->assertTrue($product->active);
    $this->assertEquals('2023-10-01', $product->createdAt);
    $this->assertEquals(5, $product->stock);
  }

  public function testSetCategories(): void
  {
    $product = new Product(1, 2, 'Test Product', 99.99, true, '2023-10-01');
    $product->setCategories(['Electronics']);

    $this->assertEquals(['Electronics'], $product->categories);
  }
}
