<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Jobs\UpdateMeilisearchIndex;

class ProductController extends Controller
{
    // Elenco dei prodotti con ricerca tramite Meilisearch
    public function index(Request $request)
    {
        $searchTerm = $request->search;

        if ($searchTerm) {
            $products = Product::search($searchTerm)->get();
        } else if ($request->has('name') || $request->has('description') || $request->has('price') || $request->has('stock_level')) {
            $query = Product::query();

            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->has('description')) {
                $query->where('description', 'like', '%' . $request->description . '%');
            }

            //TODO aggiungere possibilità di filtrare per prezzo e stock_level maggiore o minore di un certo valore
            if ($request->has('price')) {
                $operator = in_array($request->price_operator, ['>', '<', '=', '>=', '<=']) ? $request->price_operator : '=';
                $query->where('price', $operator, $request->price);
            }            
            
            if ($request->has('stock_level')) {
                $operator = in_array($request->stock_operator, ['>', '<', '=', '>=', '<=']) ? $request->stock_operator : '=';
                $query->where('stock_level', $operator, $request->stock_level);
            }

            $products = $query->get();
        } else {
            $products = Product::all();
        }

        return response()->json($products, 200);
    }

    // Dettaglio di un prodotto
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product, 200);
    }

    // Creazione di un nuovo prodotto
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_level' => 'required|integer|min:0',
        ]);

        $product = Product::create($request->all());
        
        // Dispatch job to update the search index for the new product
        UpdateMeilisearchIndex::dispatch($product);

        return response()->json($product, 201);
    }

    // Modifica di un prodotto
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_level' => 'required|integer|min:0',
        ]);
        $product->update($request->all());
        
        // Invia il job in coda per aggiornare l’indice
        UpdateMeilisearchIndex::dispatch($product);

        return response()->json($product, 200);
    }

    // Eliminazione di un prodotto
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Clonare il prodotto prima di eliminarlo per poterlo passare al job
        $productClone = clone $product;
        
        $product->delete();
        // dispatch il job per aggiornare l’indice di ricerca
        UpdateMeilisearchIndex::dispatch($productClone);

        return response()->json(['message' => 'Product deleted'], 200);
    }
}
