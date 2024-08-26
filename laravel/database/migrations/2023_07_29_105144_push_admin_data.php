<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PushAdminData extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    $data = [
      [
        'account' => 'admin',
        'nickname' => '超级管理员',
        'password' => bcrypt('000000'),
        'admin_auth_group' => -1,
      ],
    ];
    foreach ($data as $datum) {
      $admin = new App\Models\Admin();
      $admin->nickname = $datum['nickname'];
      $admin->admin_auth_group = $datum['admin_auth_group'];
      $admin->save();
      $admin_account = new App\Models\AdminAccount();
      $admin_account->admin = $admin->id;
      $admin_account->account = $datum['account'];
      $admin_account->secret = $datum['password'];
      $admin_account->type = 1;
      $admin_account->save();
    }
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    //
  }
}
