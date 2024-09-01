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
    Schema::create('admin_tokens', function (Blueprint $table) {
      $table->id();
      $table->bigInteger('admin')->index()->comment('账号ID');
      $table->string('token', 50)->comment('TOKEN')->index();
      $table->string('ip', 20)->comment('IP')->index();
      $table->string('region', 50)->comment('IP地区信息');
      $table->tinyInteger('type')->comment('1-后台');
      $table->tinyInteger('del')->default(2)->comment('1-删除 2-正常');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('admin_tokens');
  }
};
