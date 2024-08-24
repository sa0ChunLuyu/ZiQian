<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Lib\Zi;
use App\Lib\ZiQian;

class YoController extends Controller
{
  public function test()
  {
    return Zi::echo([
      'name' => env('APP_NAME'),
      'datetime' => date('Y-m-d H:i:s'),
      'ip' => ZiQian::ip(),
      'data' => request()->all()
    ]);
  }
}
