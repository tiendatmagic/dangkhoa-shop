<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPayout extends Model
{
    protected $table = 'order_payouts';

    protected $fillable = [
        'order_id',
        'asset_symbol',
        'chain_id',
        'is_native',
        'token_address',
        'to_address',
        'total_usd',
        'price_usd',
        'amount_decimal',
        'amount_wei',
        'tx_hash',
        'error',
        'sent_at',
    ];

    protected $casts = [
        'chain_id' => 'integer',
        'is_native' => 'boolean',
        'sent_at' => 'datetime',
    ];
}
