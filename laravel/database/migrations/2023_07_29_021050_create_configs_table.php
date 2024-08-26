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
      $table->id()->comment('db system 系统配置');
      $table->string('name', 50)->index();
      $table->longText('value');
      $table->tinyInteger('type')->comment('1-文字 2-图片 3-文字数组 4-图片数组 5-JSON 6-富文本 7-开关 8-颜色')->index();
      $table->tinyInteger('client')->comment('类型 0-公共 1-后台')->index();
      $table->tinyInteger('login')->comment('登录类型 1-登录获取 2-随时获取')->index();
      $table->string('remark', 100);
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
