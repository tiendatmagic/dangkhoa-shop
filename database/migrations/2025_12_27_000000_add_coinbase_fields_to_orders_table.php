<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'coinbase_charge_id')) {
                    $table->string('coinbase_charge_id')->nullable()->after('txhash');
                }
                if (!Schema::hasColumn('orders', 'coinbase_hosted_url')) {
                    $table->string('coinbase_hosted_url')->nullable()->after('coinbase_charge_id');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'coinbase_hosted_url')) {
                    $table->dropColumn('coinbase_hosted_url');
                }
                if (Schema::hasColumn('orders', 'coinbase_charge_id')) {
                    $table->dropColumn('coinbase_charge_id');
                }
            });
        }
    }
};
