<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('admin_wallet_settings', function (Blueprint $table) {
      $table->id();
      $table->text('from_address')->nullable();
      $table->text('private_key')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('admin_wallet_settings');
  }
};
