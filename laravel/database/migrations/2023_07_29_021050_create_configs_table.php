<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('configs', function (Blueprint $table) {
      $table->id();
      $table->string('name', 50)->index();
      $table->longText('value');
      $table->string('type', 30)->index();
      $table->tinyInteger('client')->comment('类型 0-公共 1-后台')->index();
      $table->tinyInteger('login')->comment('登录类型 1-登录获取 2-随时获取')->index();
      $table->string('desc', 100);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('configs');
  }
};
