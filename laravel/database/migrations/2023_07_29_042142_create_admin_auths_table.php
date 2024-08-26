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
    Schema::create('admin_auths', function (Blueprint $table) {
      $table->id()->comment('db disable');
      $table->string('name', 200)->comment('名称')->index();
      $table->string('title', 20)->comment('显示标题');
      $table->string('icon', 100)->default('')->comment('显示图标');
      $table->integer('pid')->comment('上级ID');
      $table->tinyInteger('type')->comment('1-分组 2-页面/接口');
      $table->tinyInteger('check')->comment('1-需要验证 2-不需要验证');
      $table->tinyInteger('show')->default(1)->comment('1-显示 2-不显示');
      $table->tinyInteger('status')->default(1)->comment('1-正常 2-禁用');
      $table->tinyInteger('del')->default(2)->comment('1-删除 2-正常');
      $table->string('message', 50)->comment('验证失败的提示信息');
      $table->integer('order')->default(0)->comment('排序');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('admin_auths');
  }
};
