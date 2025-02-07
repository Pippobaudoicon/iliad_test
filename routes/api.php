<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;

Route::apiResource('orders', OrderController::class);

Route::apiResource('products', ProductController::class);

Route::get('/ping', function () {
    return response()->json(['message' => 'API is working!']);
});
