<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_payouts', function (Blueprint $table) {
            $table->id();
            $table->uuid('order_id');
            $table->string('asset_symbol', 32);
            $table->unsignedInteger('chain_id')->default(56);
            $table->boolean('is_native')->default(false);
            $table->string('token_address', 42)->nullable();
            $table->string('to_address', 42);

            $table->decimal('total_usd', 24, 8)->nullable();
            $table->decimal('price_usd', 24, 12)->nullable();
            $table->string('amount_decimal', 64)->nullable();
            $table->string('amount_wei', 128)->nullable();

            $table->string('tx_hash', 128)->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->unique(['order_id', 'asset_symbol']);
            $table->index(['order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payouts');
    }
};
