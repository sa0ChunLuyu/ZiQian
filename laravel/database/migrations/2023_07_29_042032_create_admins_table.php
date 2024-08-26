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
    Schema::create('admins', function (Blueprint $table) {
      $table->id()->comment('db disable');
      $table->string('nickname', 50)->comment('名称');
      $table->string('avatar', 200)->default('')->comment('头像');
      $table->integer('admin_auth_group')->comment('权限组ID')->index();
      $table->tinyInteger('initial_password')->default(1)->comment('1-下次登录需要修改密码 2-已经修改密码');
      $table->tinyInteger('status')->default(1)->comment('1-正常 2-禁用');
      $table->tinyInteger('del')->default(2)->comment('1-删除 2-正常');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('admins');
  }
};
