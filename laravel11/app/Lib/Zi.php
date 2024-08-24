<?php

namespace App\Lib;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

class Zi
{
  public static function cco($id = 0)
  {
    return ZiQian::echo(config('code.200.c'), 200, ['id' => $id]);
  }

  public static function dco($id = 0)
  {
    return ZiQian::echo(config('code.200.d'), 200, ['id' => $id]);
  }

  public static function uco($id = 0)
  {
    return ZiQian::echo(config('code.200.u'), 200, ['id' => $id]);
  }

  public static function eco($code, $replace = [])
  {
    $msg = config("code.{$code}");
    if (count($replace)) $msg = Str::replaceArray('?', $replace, $msg);
    throw new HttpResponseException(ZiQian::echo($msg, $code));
  }

  public static function debug($data)
  {
    throw new HttpResponseException(ZiQian::echo('Debug', 100000, $data));
  }

  public static function echo($data = [])
  {
    return ZiQian::echo(config('code.200.r'), 200, $data);
  }
}
