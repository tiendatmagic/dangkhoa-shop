<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;
    protected $fillable = ['orders'];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
    ];
}
