<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model as M;

class Model extends M
{
  public function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }
}
