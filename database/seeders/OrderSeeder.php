<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Product;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // Crea 10 ordini casuali
        Order::factory()->count(10)->create()->each(function ($order) {
            // Collega l'ordine a 1-3 prodotti casuali
            $products = Product::inRandomOrder()->take(rand(1, 3))->get();

            foreach ($products as $product) {
                $order->products()->attach($product->id, [
                    'quantity' => rand(1, 5)  // Quantità casuale da 1 a 5
                ]);
            }
        });
    }
}
