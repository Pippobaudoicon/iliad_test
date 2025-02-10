<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order()
    {
        $product = Product::factory()->create(['stock_level' => 10]);

        $orderData = [
            'customer_name' => 'John Doe',
            'description' => 'Order for laptops',
            'products' => [
                [
                    'id' => $product->id,
                    'quantity' => 2
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
                ->assertJson([
                    'customer_name' => 'John Doe',
                    'description' => 'Order for laptops',
                ]);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'John Doe',
            'description' => 'Order for laptops',
        ]);

        $this->assertDatabaseHas('order_product', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);
    }

    public function test_cannot_create_order_with_insufficient_stock()
    {
        $product = Product::factory()->create(['stock_level' => 1]);

        $orderData = [
            'customer_name' => 'John Doe',
            'description' => 'Order for laptops',
            'products' => [
                [
                    'id' => $product->id,
                    'quantity' => 2
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(400);
    }

    public function test_can_update_order()
    {
        $product = Product::factory()->create(['stock_level' => 10]);
        $order = Order::factory()->create();
        $order->products()->attach($product->id, ['quantity' => 1]);

        $updateData = [
            'customer_name' => 'John Doe',
            'description' => 'Order for laptops',
            'products' => [
                [
                    'id' => $product->id,
                    'quantity' => 3
                ]
            ]
        ];

        $response = $this->putJson("/api/orders/{$order->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'customer_name' => 'John Doe',
                    'description' => 'Order for laptops',
                ]);
    }

    public function test_can_delete_order()
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create(['stock_level' => 10]);
        $order->products()->attach($product->id, ['quantity' => 1]);

        $response = $this->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('order_product', [
            'order_id' => $order->id,
            'product_id' => $product->id
        ]);
    }

    public function test_can_list_orders()
    {
        Order::factory()->count(3)->create();

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
                ->assertJsonCount(3);
    }
}