<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('token_assets', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 32)->unique();
            $table->unsignedInteger('chain_id')->default(56);
            $table->boolean('is_native')->default(false);
            $table->string('token_address', 42)->nullable();
            $table->unsignedSmallInteger('decimals')->default(18);
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('token_assets');
    }
};
