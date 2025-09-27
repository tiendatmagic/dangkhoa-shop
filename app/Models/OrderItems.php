<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    use HasFactory;
    protected $fillable = ['order_items'];

    protected $casts = [
        'id' => 'string',
        'order_id' => 'string',
        'user_id' => 'string'
    ];
}
