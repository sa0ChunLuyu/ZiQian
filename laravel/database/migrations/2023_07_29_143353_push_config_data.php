<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PushConfigData extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    $data = [[
      'name' => 'Logo',
      'value' => '/storage/assets/default/logo.png',
      'type' => 'image',
      'client' => '0',
      'login' => '2',
    ], [
      'name' => 'Favicon',
      'value' => '/storage/assets/default/favicon.png',
      'type' => 'image',
      'client' => '0',
      'login' => '2',
    ], [
      'name' => 'Login欢迎图片',
      'value' => '/storage/assets/default/login_cover.png',
      'type' => 'image',
      'client' => '1',
      'login' => '2',
    ], [
      'name' => 'Login背景图',
      'value' => '/storage/assets/default/login_bg.png',
      'type' => 'image',
      'client' => '1',
      'login' => '2',
    ], [
      'name' => 'Login背景色',
      'value' => '#16baaa',
      'type' => 'color',
      'client' => '1',
      'login' => '2',
    ], [
      'name' => '网站名称',
      'value' => env('APP_NAME'),
      'type' => 'string',
      'client' => '0',
      'login' => '2',
    ], [
      'name' => '后台图形验证',
      'value' => '1',
      'type' => 'switch',
      'client' => '1',
      'login' => '2',
      'remark' => '',
    ], [
      'name' => '后台IP地区信息',
      'value' => '1',
      'type' => 'switch',
      'client' => '1',
      'login' => '2',
      'remark' => '后台 TOKEN IP 地区信息存储',
    ], [
      'name' => '后台账号单点登录',
      'value' => '0',
      'type' => 'switch',
      'client' => '1',
      'login' => '2',
      'remark' => '',
    ]];
    foreach ($data as $datum) {
      $config = new App\Models\Config();
      $config->name = $datum['name'];
      $config->value = $datum['value'];
      $config->type = $datum['type'];
      $config->client = $datum['client'];
      $config->login = $datum['login'];
      $config->remark = $datum['remark'] ?? '';
      $config->save();
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
