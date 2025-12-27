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
                if (!Schema::hasColumn('orders', 'coinbase_expires_at')) {
                    $table->timestamp('coinbase_expires_at')->nullable()->after('coinbase_hosted_url');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'coinbase_expires_at')) {
                    $table->dropColumn('coinbase_expires_at');
                }
            });
        }
    }
};
