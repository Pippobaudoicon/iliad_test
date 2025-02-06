<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Elenco degli ordini con filtro per nome e descrizione
    public function index(Request $request)
    {
        $query = Order::query();

        if ($request->has('name')) {
            $query->where('customer_name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }

        return response()->json($query->get(), 200);
    }

    // Dettaglio di un ordine
    public function show($id)
    {
        $order = Order::find($id);

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
        ]);

        $order = Order::create($request->all());

        return response()->json($order, 201);
    }

    // Modifica di un ordine
    public function update(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->update($request->all());

        return response()->json($order, 200);
    }

    // Eliminazione di un ordine
    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted'], 200);
    }
}
