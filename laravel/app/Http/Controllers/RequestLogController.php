<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Lib\Zi;
use App\Lib\ZiQian;
use App\Lib\Token;
use Illuminate\Http\Request;

class RequestLogController extends Controller
{
  /***auto route
   * name: txt
   * type: admin
   * method: post
   */
  public function txt(Request $request)
  {
    Token::admin(['config-log']);
    $id = $request->post('id');
    $type = $request->post('type');
    $log = DB::table('zz_request_log_' . date('ym', strtotime($request->post('created_at'))))->where('id', $id)->first();
    if (!$log) Zi::eco(100001, ['日志']);
    $content = file_get_contents(base_path("/storage/app/$log->input"));
    $sections = explode('-------------------------------', $content);
    $data = '{}';
    switch ($type) {
      case 'input':
        $post = self::self_txtClear($sections[0], 1);
        $header = self::self_txtClear($sections[1], 1);
        $data = '{
  "params":' . $log->params . ',
  "input":' . $post . ',
  "header":' . $header . '
}';
        break;
      case 'result':
        $result = self::self_txtClear($sections[2], 0);
        $data = $result;
        break;
      case 'curl':
        $post = self::self_txtClear($sections[0], 1);
        $header = self::self_txtClear($sections[1], 1);
        $result = self::self_txtClear($sections[2], 0);
        $data = '{
  "url":"' . $log->url . '",
  "method":"' . $log->method . '",
  "params":' . $log->params . ',
  "input":' . $post . ',
  "header":' . $header . ',
  "result":' . $result . '
}';
        break;
    }
    return Zi::echo([
      'data' => $data,
    ]);
  }

  public function self_txtClear($content, $index)
  {
    $content_arr = explode("\n", $content);
    return $content_arr[count($content_arr) - 1 - $index];
  }

  /***auto route
   * name: list
   * type: admin
   * method: post
   * query: ?page={page}
   */
  public function list(Request $request)
  {
    Token::admin(['config-log']);
    $search = $request->post('search');
    $time = $request->post('time');
    $method = $request->post('method');
    $code = $request->post('code');
    $start_time = !!$time[0] ? ZiQian::date(strtotime($time[0]), 'Y-m-d') : date('Y-m-01');
    $end_time = !!$time[1] ? ZiQian::date(strtotime($time[1]), 'Y-m-d') : date('Y-m-t');
    $ym_check = [
      date('Y-m', strtotime($start_time)),
      date('Y-m', strtotime($end_time)),
    ];
    if ($ym_check[0] !== $ym_check[1]) Zi::eco(100023);
    $table_name = 'zz_request_log_' . date('ym', strtotime($start_time));
    $table_count = DB::select('select count(1) as c from information_schema.TABLES where table_schema = ? and table_name = ?', [env('DB_DATABASE'), $table_name])[0];
    if ($table_count->c === 0) Zi::eco(100001, ['日志表']);
    $request_list = DB::table($table_name)->where(function ($query) use ($search) {
      if ($search != '') $query->where('uuid', $search)
        ->orWhere('token', $search)
        ->orWhere('url', $search)
        ->orWhere('ip', $search);
    })
      ->where(function ($query) use ($start_time) {
        if ($start_time != '') $query->where('created_at', '>=', $start_time . ' 00:00:00');
      })
      ->where(function ($query) use ($end_time) {
        if ($end_time != '') $query->where('created_at', '<=', $end_time . ' 23:59:59');
      })
      ->where(function ($query) use ($method) {
        if ($method != '' && $method != 'null') $query->where('method', $method);
      })
      ->where(function ($query) use ($code) {
        if ($code != '') $query->where('code', $code);
      })
      ->orderBy('id', 'desc')
      ->paginate(20);
    return Zi::echo([
      'list' => $request_list,
      'time' => [$start_time, $end_time],
    ]);
  }
}
