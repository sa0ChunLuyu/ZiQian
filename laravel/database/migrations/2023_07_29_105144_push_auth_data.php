<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PushAuthData extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    $data = [[
      'name' => 'admin_basics',
      'title' => '后台基础接口',
      'icon' => '',
      'pid' => '0',
      'type' => '1',
      'check' => '2',
      'show' => '2',
    ], [
      'name' => 'admin_basics_login',
      'title' => '后台登录',
      'icon' => '',
      'pid' => '1',
      'type' => '2',
      'check' => '1',
      'show' => '2',
      'message' => '该账号所属权限组已被禁止登录后台'
    ], [
      'name' => '/admin',
      'title' => '人员设置',
      'icon' => 'every-user',
      'pid' => '0',
      'type' => '1',
      'check' => '2',
      'show' => '1',
      'message' => ''
    ], [
      'name' => '/admin/auth',
      'title' => '权限管理',
      'icon' => 'personal-privacy',
      'pid' => '3',
      'type' => '2',
      'check' => '1',
      'show' => '1',
      'message' => '该账号所属权限组不能对权限分组进行设置'
    ], [
      'name' => '/admin/list',
      'title' => '人员列表',
      'icon' => 'every-user',
      'pid' => '3',
      'type' => '2',
      'check' => '1',
      'show' => '1',
      'message' => '该账号所属权限组不能对人员进行设置'
    ], [
      'name' => '/config',
      'title' => '参数配置',
      'icon' => 'setting',
      'pid' => '0',
      'type' => '1',
      'check' => '2',
      'show' => '1',
    ], [
      'name' => '/config/config',
      'title' => '后台配置',
      'icon' => 'setting-config',
      'pid' => '6',
      'type' => '2',
      'check' => '1',
      'show' => '1',
      'message' => '该账号所属权限组不能对参数配置进行设置'
    ], [
      'name' => '/config/router',
      'title' => '路由配置',
      'icon' => 'left-and-right-branch',
      'pid' => '6',
      'type' => '2',
      'check' => '1',
      'show' => '1',
      'message' => '该账号所属权限组不能对路由进行设置'
    ], [
      'name' => '/config/log',
      'title' => '请求日志',
      'icon' => 'log',
      'pid' => '6',
      'type' => '2',
      'check' => '1',
      'show' => '1',
      'message' => '该账号所属权限组不能查看请求日志'
    ], [
      'name' => '/config/upload',
      'title' => '上传管理',
      'icon' => 'upload',
      'pid' => '6',
      'type' => '2',
      'check' => '1',
      'show' => '1',
      'message' => '该账号所属权限组不能查看上传管理'
    ], [
      'name' => '/config/ip',
      'title' => 'IP解析库',
      'icon' => 'ethernet-on',
      'pid' => '6',
      'type' => '2',
      'check' => '1',
      'show' => '1',
      'message' => '该账号所属权限组不能查看IP解析库'
    ]];
    foreach ($data as $datum) {
      $auth = new App\Models\AdminAuth();
      $auth->name = $datum['name'];
      $auth->title = $datum['title'];
      $auth->icon = $datum['icon'];
      $auth->pid = $datum['pid'];
      $auth->type = $datum['type'];
      $auth->check = $datum['check'];
      $auth->show = $datum['show'];
      $auth->message = $datum['message'] ?? '';
      $auth->status = 1;
      $auth->del = 2;
      $auth->order = 0;
      $auth->save();
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
