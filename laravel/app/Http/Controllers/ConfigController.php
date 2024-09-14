<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;
use App\Lib\Zi;
use App\Lib\Token;

class ConfigController extends Controller
{
  /***auto route
   * name: edit
   * type: admin
   * method: post
   */
  public function edit(Request $request)
  {
    Token::admin(['/config/setting']);
    $name = $request->get('name');
    $value = $request->get('value');
    $config = Config::where('name', $name)->first();
    if (!!$config) {
      $config->value = $value;
      $config->save();
    }
    return Zi::echo();
  }

  /***auto route
   * name: get
   * type: admin
   * method: post
   * query: ?client={client}
   */
  public function get(Request $request)
  {
    $client = $request->get('client');
    if (!$client) $client = 'public';
    $client_map = [
      'public' => 0,
      'admin' => 1,
    ];
    $client_number = $client_map[$client];
    $config_arr = $request->post('config_arr');
    if (!$config_arr) $config_arr = [];
    $configs = $this->self_getConfigList($config_arr, $client_number);
    return Zi::echo($configs);
  }

  public function self_getConfigList($arr, $client)
  {
    $config_arr = [];
    foreach ($arr as $item) $config_arr[$item] = '';
    $config_db = Config::whereIn('name', $arr);
    if ($client != 0) $config_db->whereIn('client', [0, $client]);
    $config = $config_db->get();
    foreach ($config as $item) {
      $value = $item->value;
      if (in_array($item->type, ['stringArray', 'imageArray', 'json', 'switch'])) {
        $value = json_decode($value, true);
      }
      $config_arr[$item->name] = $value;
    }
    return $config_arr;
  }
}
