<?php

use App\Lib\ZiQian;
use Illuminate\Support\Facades\Route;

$route_map = ZiQian::auto_route();
foreach ($route_map as $item) {
  $url = 'api/' . ucfirst($item['type']) . '/' . $item['class'] . '/' . $item['name'];
  $method = 'Route::' . $item['method'];
  $method($url, '\App\Http\Controllers\\' . $item['class'] . 'Controller@' . $item['name']);
}
Route::post('api/map', [\App\Http\Controllers\ApiController::class, 'map']);
Route::any('api/yo', [\App\Http\Controllers\YoController::class, 'test']);
Route::get('/', function () {
  return view('welcome');
});
