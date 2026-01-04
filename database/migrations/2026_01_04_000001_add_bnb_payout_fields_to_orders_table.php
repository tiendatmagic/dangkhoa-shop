<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::table('orders', function (Blueprint $table) {
      if (! Schema::hasColumn('orders', 'bnb_sent_at')) {
        $table->timestamp('bnb_sent_at')->nullable()->after('paid_at');
      }
      if (! Schema::hasColumn('orders', 'bnb_send_txhash')) {
        $table->string('bnb_send_txhash')->nullable()->after('bnb_sent_at');
      }
      if (! Schema::hasColumn('orders', 'bnb_send_error')) {
        $table->text('bnb_send_error')->nullable()->after('bnb_send_txhash');
      }
    });
  }

  public function down(): void
  {
    Schema::table('orders', function (Blueprint $table) {
      if (Schema::hasColumn('orders', 'bnb_send_error')) {
        $table->dropColumn('bnb_send_error');
      }
      if (Schema::hasColumn('orders', 'bnb_send_txhash')) {
        $table->dropColumn('bnb_send_txhash');
      }
      if (Schema::hasColumn('orders', 'bnb_sent_at')) {
        $table->dropColumn('bnb_sent_at');
      }
    });
  }
};
