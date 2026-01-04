<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminWalletSetting extends Model
{
  protected $table = 'admin_wallet_settings';

  protected $fillable = [
    'chain_id',
    'rpc_url',
    'contract_address',
    'from_address',
    'private_key',
  ];

  protected $casts = [
    'chain_id' => 'integer',
    'from_address' => 'encrypted',
    'private_key' => 'encrypted',
  ];
}
