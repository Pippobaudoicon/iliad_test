<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Elenco degli ordini con filtro per nome e descrizione
    public function index(Request $request)
    {
        $searchTerm = $request->search;

        if ($searchTerm) {
            $orders = Order::search($searchTerm)->get()->load('products');
        } else if($request->has('name') || $request->has('description')) {
            $query = Order::query();

            if ($request->has('name')) {
                $query->where('customer_name', 'like', '%' . $request->name . '%');
            }

            if ($request->has('description')) {
                $query->where('description', 'like', '%' . $request->description . '%');
            }

            $orders = $query->with('products')->get();
        } else {
            $orders = Order::with('products')->get();
        }

        return response()->json($orders, 200);
    }

    // Dettaglio di un ordine
    public function show($id)
    {
        $order = Order::with('products')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order, 200);
    }

    // Creazione di un nuovo ordine
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);
    
        $order = Order::create($request->only('customer_name', 'description'));
    
        foreach ($request->products as $productData) {
            // Ottieni il prodotto con lock pessimistico
            $product = Product::where('id', $productData['id'])->lockForUpdate()->first();
    
            // Verifica se c'è abbastanza stock
            if ($product->stock_level < $productData['quantity']) {
                return response()->json([
                    'message' => "Insufficient stock for product: {$product->name}"
                ], 400);
            }
    
            // Decrementa lo stock e collega il prodotto all'ordine
            $product->decrement('stock_level', $productData['quantity']);
            $order->products()->attach($product->id, ['quantity' => $productData['quantity']]);
        }
    
        return response()->json($order->load('products'), 201);
    }
    
    

    // Modifica di un ordine
    public function update(Request $request, $id)
    {
        $order = Order::with('products')->find($id);
    
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
    
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);
    
        // Ripristina lo stock dei prodotti dell'ordine precedente
        foreach ($order->products as $product) {
            $product->increment('stock_level', $product->pivot->quantity);
        }
    
        $order->update($request->only('customer_name', 'description'));
        $order->products()->detach();
    
        foreach ($request->products as $productData) {
            // Ottieni il prodotto con lock pessimistico
            $product = Product::where('id', $productData['id'])->lockForUpdate()->first();
    
            if ($product->stock_level < $productData['quantity']) {
                return response()->json([
                    'message' => "Insufficient stock for product: {$product->name}"
                ], 400);
            }
    
            // Decrementa lo stock e aggiorna la relazione
            $product->decrement('stock_level', $productData['quantity']);
            $order->products()->attach($product->id, ['quantity' => $productData['quantity']]);
        }
    
        return response()->json($order->load('products'), 200);
    }
    
    

    // Eliminazione di un ordine
    public function destroy($id)
    {
        $order = Order::with('products')->find($id);
    
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }
    
        // Ripristina lo stock dei prodotti
        foreach ($order->products as $product) {
            $product->increment('stock_level', $product->pivot->quantity);
        }
    
        $order->products()->detach();
        $order->delete();
    
        return response()->json(['message' => 'Order deleted'], 200);
    }    
}
