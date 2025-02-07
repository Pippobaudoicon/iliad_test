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
        $response = $this->postJson('/api/products', [
            'name' => 'Laptop',
            'description' => 'High-end gaming laptop',
            'price' => 1500,
            'stock_level' => 10
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'name' => 'Laptop',
                     'description' => 'High-end gaming laptop'
                 ]);
    }

    public function test_can_get_product_list()
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonCount(5);
    }

    public function test_can_update_product()
    {
        $product = Product::factory()->create();

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Updated Laptop',
            'description' => 'Updated description',
            'price' => 1800,
            'stock_level' => 5
        ]);

        $response->assertStatus(200)
                 ->assertJson(['name' => 'Updated Laptop']);
    }
}
