<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Jobs\UpdateMeilisearchIndex;
use Illuminate\Support\Facades\DB;
use Exception;

class StockException extends Exception {} // Custom exception for stock issues

class OrderService
{
    public function createOrder(array $data)
    {
        return DB::transaction(function() use ($data) {
            // Create a new order record
            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'description' => $data['description'] ?? null,
            ]);

            // Process each ordered product
            foreach ($data['products'] as $productData) {
                $product = Product::where('id', $productData['id'])->lockForUpdate()->first();

                if ($product->stock_level < $productData['quantity']) {
                    throw new StockException("Insufficient stock for product: {$product->name}");
                }

                $this->updateStockAndDispatch($product, $productData['quantity']);
                $order->products()->attach($product->id, ['quantity' => $productData['quantity']]);
            }

            return $order;
        });
    }

    public function updateOrder(Order $order, array $data)
    {
        return DB::transaction(function() use ($order, $data) {
            // Restore stock from previously attached products
            foreach ($order->products as $product) {
                $product->increment('stock_level', $product->pivot->quantity);
            }

            // Update order details and remove previous product associations
            $order->update([
                'customer_name' => $data['customer_name'],
                'description' => $data['description'] ?? null,
            ]);
            $order->products()->detach();

            // Process new product associations with stock check
            foreach ($data['products'] as $productData) {
                $product = Product::where('id', $productData['id'])->lockForUpdate()->first();

                if ($product->stock_level < $productData['quantity']) {
                    throw new StockException("Insufficient stock for product: {$product->name}");
                }

                $this->updateStockAndDispatch($product, $productData['quantity']);
                $order->products()->attach($product->id, ['quantity' => $productData['quantity']]);
            }

            return $order;
        });
    }
    
    // Helper to decrement stock and dispatch the update job
    protected function updateStockAndDispatch(Product $product, int $quantity): void
    {
        $product->decrement('stock_level', $quantity);
        UpdateMeilisearchIndex::dispatch($product);
    }
}
