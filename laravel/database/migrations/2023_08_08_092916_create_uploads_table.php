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
    Schema::create('uploads', function (Blueprint $table) {
      $table->id();
      $table->string('uuid', 80)->comment('UUID')->index();
      $table->string('name', 100)->comment('文件名')->index();
      $table->string('path', 200)->comment('路径');
      $table->string('url', 200)->comment('访问链接');
      $table->string('from', 100)->comment('上传接口')->index();
      $table->decimal('size', 20, 4)->comment('大小');
      $table->string('ext', 10)->comment('后缀')->index();
      $table->string('md5', 80)->comment('MD5')->index();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('uploads');
  }
};
