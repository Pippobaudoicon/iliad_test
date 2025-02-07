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
        // Crea prodotti fittizi
        $products = Product::factory()->count(2)->create();

        // Effettua una richiesta POST per creare un ordine
        $response = $this->postJson('/api/orders', [
            'customer_name' => 'John Doe',
            'description' => 'Order for laptops',
            'products' => [
                ['id' => $products[0]->id, 'quantity' => 2],
                ['id' => $products[1]->id, 'quantity' => 1]
            ]
        ]);

        // Verifica che la risposta sia 201 (Created)
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'id', 'customer_name', 'description', 'products'
                 ]);
    }

    public function test_can_get_order_details()
    {
        $order = Order::factory()->create();

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $order->id,
                     'customer_name' => $order->customer_name
                 ]);
    }

    public function test_returns_404_for_non_existing_order()
    {
        $response = $this->getJson('/api/orders/999');

        $response->assertStatus(404);
    }
}
