<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'price', 'category', 'product_type', 'is_best_seller', 'quantity', 'size', 'image', 'description'];

    protected $casts = [
        'id' => 'string',
    ];
}
