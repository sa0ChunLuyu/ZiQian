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
    Schema::create('admin_accounts', function (Blueprint $table) {
      $table->id();
      $table->bigInteger('admin')->index();
      $table->string('account', 50)->index();
      $table->string('secret', 80);
      $table->tinyInteger('type')->comment('1-账号密码');
      $table->tinyInteger('del')->default(2)->comment('1-删除 2-正常');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('admin_accounts');
  }
};
