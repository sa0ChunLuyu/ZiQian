<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Lib\ZiQian;
use App\Lib\Zi;
use App\Lib\Token;

class UploadController extends Controller
{
  /***auto route
   * name: search
   * type: admin
   * method: post
   */
  public function search()
  {
    $ext = Upload::select('ext')->groupBy('ext')->get();
    $from = ['AdminImage'];
    return Zi::echo([
      'ext' => $ext,
      'from' => $from,
    ]);
  }

  /***auto route
   * name: list
   * type: admin
   * method: post
   */
  public function list(Request $request)
  {
    Token::admin(['config-upload']);
    $search = $request->post('search');
    $time = $request->post('time');
    $start_time = !!$time[0] ? ZiQian::date(strtotime($time[0] . ' 00:00:00')) : '';
    $end_time = !!$time[1] ? ZiQian::date(strtotime($time[1] . ' 23:59:59')) : '';
    $ext = $request->post('ext');
    $from = $request->post('from');
    $from_map = [
      'AdminImage' => '/api/Admin/Upload/image',
    ];
    $from_search = '';
    if (!!$from) $from_search = $from_map[$from];
    $upload_list = Upload::where(function ($query) use ($search) {
      if ($search != '') $query->where('uuid', $search)
        ->orWhere('name', $search)
        ->orWhere('md5', $search);
    })
      ->where(function ($query) use ($start_time) {
        if ($start_time != '') $query->where('created_at', '>=', $start_time);
      })
      ->where(function ($query) use ($end_time) {
        if ($end_time != '') $query->where('created_at', '<=', $end_time);
      })
      ->where(function ($query) use ($ext) {
        if ($ext != '') $query->where('ext', $ext);
      })
      ->where(function ($query) use ($from_search) {
        if ($from_search != '') $query->where('from', $from_search);
      })
      ->orderBy('id', 'desc')
      ->paginate(20);
    return Zi::echo([
      'list' => $upload_list
    ]);
  }

  /***auto route
   * name: delete
   * type: admin
   * method: post
   */
  public function delete(Request $request)
  {
    Token::admin(['config-upload']);
    $id = $request->post('id');
    $upload = Upload::where('id', $id)->first();
    if (!$upload) Zi::eco(100001, ['上传文件']);
    $upload->delete();
    unlink($upload->path);
    return Zi::dco($upload->id);
  }

  /***auto route
   * name: image
   * type: admin
   * method: post
   */
  public function image(Request $request)
  {
    $base64 = $request->post('base64');
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)) {
      $type = ['png', 'jpeg', 'jpg', 'gif'];
      if (!in_array($result[2], $type)) Zi::eco(100015);
      $md5 = md5($base64);
      $upload = Upload::where('md5', $md5)->first();
      if (!$upload) {
        $disk = Storage::disk('public');
        $name = Str::orderedUuid();
        $date = date('Y/m');
        $path = "/assets/upload/image/$date/$name.$result[2]";
        $put = $disk->put($path, base64_decode(str_replace($result[1], '', $base64)));
        if (!$put) Zi::eco(100016, ['put']);
        $save = "/storage/assets/upload/image/$date/$name.$result[2]";
        $size = $disk->size($path);
        $p = $disk->path($path);
        $upload = new Upload();
        $upload->uuid = $name;
        $upload->name = 'Base64-' . $md5;
        $upload->path = $p;
        $upload->url = $save;
        $upload->from = explode('?', $_SERVER['REQUEST_URI'])[0];
        $upload->size = $size / 1024 / 1024;
        $upload->ext = $result[2];
        $upload->md5 = $md5;
        $upload->save();
      }
      return Zi::echo([
        'url' => $upload->url
      ]);
    } else {
      Zi::eco(100016, ['base64']);
    }
  }
}
