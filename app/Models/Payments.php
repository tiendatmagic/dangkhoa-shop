<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    use HasFactory;
    protected $fillable = ['payments'];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
    ];
}
