<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenAsset extends Model
{
    protected $table = 'token_assets';

    protected $fillable = [
        'symbol',
        'chain_id',
        'is_native',
        'token_address',
        'decimals',
        'enabled',
    ];

    protected $casts = [
        'chain_id' => 'integer',
        'is_native' => 'boolean',
        'decimals' => 'integer',
        'enabled' => 'boolean',
    ];
}
