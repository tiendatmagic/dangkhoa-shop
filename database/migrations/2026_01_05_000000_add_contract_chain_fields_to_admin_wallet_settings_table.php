<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('admin_wallet_settings', function (Blueprint $table) {
      if (! Schema::hasColumn('admin_wallet_settings', 'chain_id')) {
        $table->unsignedBigInteger('chain_id')->default(56)->after('id');
      }
      if (! Schema::hasColumn('admin_wallet_settings', 'rpc_url')) {
        $table->string('rpc_url')->nullable()->after('chain_id');
      }
      if (! Schema::hasColumn('admin_wallet_settings', 'contract_address')) {
        $table->string('contract_address')->nullable()->after('rpc_url');
      }
    });

    // Add a unique index (best-effort: ignore if already exists)
    try {
      Schema::table('admin_wallet_settings', function (Blueprint $table) {
        $table->unique('chain_id', 'admin_wallet_settings_chain_id_unique');
      });
    } catch (\Throwable $e) {
      // ignore
    }
  }

  public function down(): void
  {
    try {
      Schema::table('admin_wallet_settings', function (Blueprint $table) {
        $table->dropUnique('admin_wallet_settings_chain_id_unique');
      });
    } catch (\Throwable $e) {
      // ignore
    }

    Schema::table('admin_wallet_settings', function (Blueprint $table) {
      if (Schema::hasColumn('admin_wallet_settings', 'contract_address')) {
        $table->dropColumn('contract_address');
      }
      if (Schema::hasColumn('admin_wallet_settings', 'rpc_url')) {
        $table->dropColumn('rpc_url');
      }
      if (Schema::hasColumn('admin_wallet_settings', 'chain_id')) {
        $table->dropColumn('chain_id');
      }
    });
  }
};
