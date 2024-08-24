<?php

namespace App\Http\Controllers;

use App\Lib\ZiQian;

abstract class Controller
{
  public function __construct()
  {
    ZiQian::requestLog();
  }
}
