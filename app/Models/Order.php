<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Campi che possono essere massivamente assegnati (mass assignment)
    protected $fillable = ['customer_name', 'description'];

    // Relazione molti-a-molti con il modello Product
    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity')->withTimestamps();
    }
}
