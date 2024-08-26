<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;
use App\Lib\Zi;

class ConfigController extends Controller
{
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
    $client_number = 0;
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
      if (in_array($item->type, [3, 4, 5])) {
        $value = json_decode($value, true);
      }
      $config_arr[$item->name] = $value;
    }
    return $config_arr;
  }
}
