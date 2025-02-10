<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_has_required_fields()
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock_level' => 10
        ]);

        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals('Test Description', $product->description);
        $this->assertEquals(99.99, $product->price);
        $this->assertEquals(10, $product->stock_level);
    }

    public function test_product_price_must_be_numeric() 
    {
        $product = Product::factory()->create();
        $this->assertIsNumeric($product->price);
    }

    public function test_product_stock_must_be_integer()
    {
        $product = Product::factory()->create();
        $this->assertIsInt($product->stock_level);
    }

    public function test_can_set_product_attributes()
    {
        $product = Product::factory()->create();
        
        $product->name = "Updated Name";
        $product->price = 199.99;
        $product->save();

        $this->assertEquals("Updated Name", $product->name);
        $this->assertEquals(199.99, $product->price);
    }

    public function test_product_can_be_instantiated()
    {
        $product = new Product;
        $this->assertInstanceOf(Product::class, $product);
    }
}
