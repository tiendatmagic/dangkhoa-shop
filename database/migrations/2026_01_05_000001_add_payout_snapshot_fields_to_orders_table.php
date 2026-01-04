<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('orders', function (Blueprint $table) {
      if (! Schema::hasColumn('orders', 'payout_type')) {
        $table->string('payout_type', 20)->nullable()->after('bnb_send_error');
      }
      if (! Schema::hasColumn('orders', 'payout_chain_id')) {
        $table->unsignedBigInteger('payout_chain_id')->nullable()->after('payout_type');
      }
      if (! Schema::hasColumn('orders', 'payout_asset_symbol')) {
        $table->string('payout_asset_symbol', 20)->nullable()->after('payout_chain_id');
      }
      if (! Schema::hasColumn('orders', 'payout_token_address')) {
        $table->string('payout_token_address')->nullable()->after('payout_asset_symbol');
      }
      if (! Schema::hasColumn('orders', 'payout_to_address')) {
        $table->string('payout_to_address')->nullable()->after('payout_token_address');
      }
      if (! Schema::hasColumn('orders', 'payout_total_usd')) {
        $table->decimal('payout_total_usd', 18, 6)->nullable()->after('payout_to_address');
      }
      if (! Schema::hasColumn('orders', 'payout_price_usd')) {
        $table->decimal('payout_price_usd', 18, 6)->nullable()->after('payout_total_usd');
      }
      if (! Schema::hasColumn('orders', 'payout_amount_decimal')) {
        $table->string('payout_amount_decimal')->nullable()->after('payout_price_usd');
      }
      if (! Schema::hasColumn('orders', 'payout_amount_wei')) {
        $table->string('payout_amount_wei')->nullable()->after('payout_amount_decimal');
      }
    });
  }

  public function down(): void
  {
    Schema::table('orders', function (Blueprint $table) {
      foreach (
        [
          'payout_amount_wei',
          'payout_amount_decimal',
          'payout_price_usd',
          'payout_total_usd',
          'payout_to_address',
          'payout_token_address',
          'payout_asset_symbol',
          'payout_chain_id',
          'payout_type',
        ] as $col
      ) {
        if (Schema::hasColumn('orders', $col)) {
          $table->dropColumn($col);
        }
      }
    });
  }
};
