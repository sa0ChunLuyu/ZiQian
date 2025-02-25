<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Lib\Zi;
use App\Lib\ZiQian;

class ApiController extends Controller
{
  public function map(Request $request)
  {
    $base_url = env('APP_URL');
    $list = [
      'apiYo' => $base_url . '/api/yo'
    ];
    $client = $request->get('client');
    if (!$client) $client = 'public';
    $route = [];
    $client_array = ['admin'];
    if (in_array($client, $client_array)) {
      $route_map = ZiQian::auto_route();
      foreach ($route_map as $item) {
        if ($item['type'] == $client || $item['type'] == 'open') {
          $key = ucfirst($item['type']) . $item['class'] . ucfirst($item['name']);
          $url = $base_url . '/api/' . ucfirst($item['type']) . '/' . $item['class'] . '/' . $item['name'] . $item['param'] . $item['query'];
          $route[$key] = $url;
        }
      }
    }
    $list = array_merge($list, $route);
    return Zi::echo([
      'list' => $list,
    ]);
  }
}
