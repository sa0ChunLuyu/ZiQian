<?php

namespace App\Lib;

use DirectoryIterator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ZiQian
{

  public static $log = null;
  public static $spend = 0;
  public static $path = '';

  public static function checkRequestLogTable()
  {
    $table_name = 'zz_request_log_' . date('ym');
    $table_count = DB::select('select count(1) as c from information_schema.TABLES where table_schema = ? and table_name = ?', [env('DB_DATABASE'), $table_name])[0];
    if ($table_count->c === 0) {
      Schema::create($table_name, function (Blueprint $table) {
        $table->id();
        $table->string('uuid', 50)->index();
        $table->string('token', 50)->index();
        $table->string('ip', 15)->index();
        $table->string('url', 300)->index();
        $table->string('method', 10);
        $table->longtext('params');
        $table->tinyInteger('type')->comment('1-文字 2-文件');
        $table->longtext('input');
        $table->longtext('header');
        $table->string('code', 10)->nullable();
        $table->text('result')->nullable();
        $table->decimal('spend', 6, 3)->nullable();
        $table->timestamps();
      });
    }
    self::$log = new \App\Models\RequestLog;
    self::$log->setTable($table_name);
  }

  public static function requestLog()
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS' && env('REQUEST_LOG') && !self::$log) {
      self::checkRequestLogTable();
      self::$spend = self::time();
      $token = '';
      if (!!request()->header('Authorization')) {
        $token_arr = explode('Bearer ', request()->header('Authorization'));
        $token = $token_arr[1] ?? '';
      }
      $uuid = Str::orderedUuid();
      $date = date('Y/m/d');
      self::$path = "log/$date/$uuid.txt";
      self::$log->uuid = $uuid;
      self::$log->token = $token;
      self::$log->ip = self::ip();
      self::$log->url = explode('?', $_SERVER['REQUEST_URI'])[0];
      self::$log->method = $_SERVER['REQUEST_METHOD'];
      $type = 1;
      $input_data = !!request()->post() ? json_encode(request()->post(), JSON_UNESCAPED_UNICODE) : '{}';
      $str_len = mb_strlen($input_data);
      $str_size = $str_len / 1024;
      if ($str_size > 40) $type = 2;
      $params_data = !!$_GET ? json_encode($_GET, JSON_UNESCAPED_UNICODE) : '{}';
      $header_data = !!request()->header() ? self::transformedHeaders() : '{}';
      $str_len = mb_strlen($header_data);
      $str_size = $str_len / 1024;
      if ($str_size > 40) $type = 2;
      self::$log->input = $input_data;
      self::$log->params = $params_data;
      self::$log->header = $header_data;
      self::$log->type = $type;
      self::$log->save();
    }
  }

  public static function transformedHeaders()
  {
    $header_data = request()->header();
    $header = [];
    foreach ($header_data as $key => $header_datum) {
      if (count($header_datum) == 1) {
        $header[$key] = $header_datum[0];
      } else {
        $header[$key] = $header_datum;
      }
    }
    return json_encode($header, JSON_UNESCAPED_UNICODE);
  }

  public static function ip()
  {
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
      $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
      $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
      $ip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
    $res = preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches [0] : '';
    return $res;
  }

  public static function date($time = false, $format = "Y-m-d H:i:s")
  {
    if (!$time) $time = time();
    return date($format, $time);
  }

  public static function time()
  {
    return floor(microtime(true) * 1000);
  }

  public static function exit($data = [])
  {
    $res = $data;
    if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS' && env('REQUEST_LOG') && !!self::$log) {
      $data_str = !!$data ? json_encode($data, JSON_UNESCAPED_UNICODE) : '{}';
      $str_len = strlen($data_str);
      $str_size = $str_len / 1024;
      $type = self::$log->type;
      if ($str_size > 40) $type = 2;
      if ($type == 2) {
        $input_data = self::$log->input;
        $header_data = self::$log->header;
        $disk = Storage::disk('local');
        $disk->append(self::$path, "POST:
$input_data
-------------------------------
HEADER:
$header_data
-------------------------------
RESULT:
$data_str");
        self::$log->input = self::$path;
        self::$log->header = self::$path;
        self::$log->result = self::$path;
        self::$log->type = 2;
      } else {
        self::$log->result = $data_str;
      }
      self::$log->code = (isset($data['code']) && !!$data['code']) ? $data['code'] : 0;
      self::$log->spend = (self::time() - self::$spend) / 1000;
      self::$log->save();
    }
    return response()->json($res)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
  }

  public static function echo($message = '', $code = 200, $data = [])
  {
    $return = [];
    $return['code'] = intval($code);
    if ($message) $return['message'] = $message;
    if ($data) $return['data'] = $data;
    return self::exit($return);
  }

  public static function post($url, $data, $type = 'json')
  {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    if ($type === 'data') {
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    }
    if ($type === 'json') {
      $data_string = json_encode($data, JSON_UNESCAPED_UNICODE);
      curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json; charset=utf-8',
        'Content-Length: ' . strlen($data_string)
      ]);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    }
    $r = curl_exec($curl);
    curl_close($curl);
    return $r;
  }

  public static function auto_route()
  {
    $path_array = [
      '/Http/Controllers'
    ];
    $route_map = [];
    foreach ($path_array as $path) {
      $dir_path = app_path($path);
      $controller_files = [];
      $iterator = new DirectoryIterator($dir_path);
      foreach ($iterator as $fileInfo) {
        if ($fileInfo->isFile() && $fileInfo->getExtension() === 'php') {
          $filename = $fileInfo->getFilename();
          if ($filename != 'Controller.php') {
            $controller_files[] = $fileInfo->getFilename();
          }
        }
      }
      foreach ($controller_files as $controller_file) {
        $file_path = app_path("$path/$controller_file");
        $file_content = file_get_contents($file_path);
        $class_pattern = '/class\s+(\w+)/';
        if (preg_match($class_pattern, $file_content, $class_matches)) {
          $class_full_name = $class_matches[1];
          $class_name = substr($class_full_name, 0, strlen($class_full_name) - 10);

          $block_pattern = '/\/\*\*\*auto route+(.*?)\*\//s';
          preg_match_all($block_pattern, $file_content, $blocks);
          $blocks = $blocks[1];
          if (count($blocks) != 0) {
            foreach ($blocks as $block) {
              $name_pattern = '/\* name:\s*([^\s]+)/';
              $route_name = '';
              if (preg_match($name_pattern, $block, $name_matches)) {
                $route_name = $name_matches[1];
              } else {
                throw new HttpResponseException(ZiQian::echo('Auto Api Error.', 100000));
              }
              $method_pattern = '/\* method:\s*([^\s]+)/';
              $route_method = '';
              if (preg_match($method_pattern, $block, $method_matches)) {
                $route_method = $method_matches[1];
              } else {
                throw new HttpResponseException(ZiQian::echo('Auto Api Error.', 100000));
              }
              if (!$route_name || !$route_method) {
                throw new HttpResponseException(ZiQian::echo('Auto Api Error.', 100000));
              }
              $query_pattern = '/\* query:\s*([^\s]+)/';
              if (preg_match($query_pattern, $block, $query_matches)) {
                $route_query = $query_matches[1];
              } else {
                $route_query = '';
              }
              $param_pattern = '/\* param:\s*([^\s]+)/';
              if (preg_match($param_pattern, $block, $param_matches)) {
                $route_param = $param_matches[1];
              } else {
                $route_param = '';
              }
              if (!$route_name || !$route_method) {
                throw new HttpResponseException(ZiQian::echo('Auto Api Error.', 100000));
              }
              $type_pattern = '/\* type:\s*([^\s]+)/';
              if (preg_match($type_pattern, $block, $type_matches)) {
                $route_type_array = explode(',', $type_matches[1]);
                if (count($route_type_array) != 0) {
                  foreach ($route_type_array as $route_type) {
                    $route_map[] = [
                      'name' => $route_name,
                      'method' => $route_method,
                      'type' => $route_type,
                      'class' => $class_name,
                      'query' => $route_query,
                      'param' => $route_param,
                    ];
                  }
                } else {
                  throw new HttpResponseException(ZiQian::echo('Auto Api Error.', 100000));
                }
              } else {
                throw new HttpResponseException(ZiQian::echo('Auto Api Error.', 100000));
              }
            }
          }
        } else {
          throw new HttpResponseException(ZiQian::echo('Auto Api Error.', 100000));
        }
      }
    }
    return $route_map;
  }
}
