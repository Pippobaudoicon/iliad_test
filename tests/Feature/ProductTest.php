<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_product()
    {
        $productData = [
            'name' => 'Laptop',
            'description' => 'Powerful gaming laptop',
            'price' => 998.99,
            'stock_level' => 20
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(201)
                ->assertJson($productData);
        
        $this->assertDatabaseHas('products', $productData);
    }

    public function test_can_update_product()
    {
        $product = Product::factory()->create();
        
        $updateData = [
            'name' => 'Laptop',
            'description' => 'Powerful gaming laptop',
            'price' => 998.99,
            'stock_level' => 20
        ];

        $response = $this->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson($updateData);
        
        $this->assertDatabaseHas('products', $updateData);
    }

    public function test_can_delete_product()
    {
        $product = Product::factory()->create();
        $response = $this->deleteJson("/api/products/{$product->id}");
        
        $response->assertStatus(404);
        
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_can_list_products()
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                ->assertJsonCount(3);
    }
}