<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Order extends Model
{
    use HasFactory, Searchable;

    protected $fillable = ['customer_name', 'description'];

    public function products()
    {
        return $this->belongsToMany(\App\Models\Product::class)
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            'description' => $this->description,
            'products' => $this->load('products')->products->map->only(['id', 'name', 'description', 'price', 'stock_level'])->all()
        ];
    }
}
