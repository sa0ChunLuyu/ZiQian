<?php

namespace App\Http\Controllers;

use App\Models\ImageCaptcha;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Lib\Zi;

class ImageCaptchaController extends Controller
{
  public function check($hash, $code, $time, $uuid)
  {
    $code = mb_strtoupper($code);
    $captcha = ImageCaptcha::where('uuid', $uuid)->first();
    if ($captcha) return 100005;
    if (time() - $time > 60 * 3) return 100005;
    if (Hash::check($code . ',Captcha,' . $uuid . ',' . $time, $hash)) {
      $captcha = new ImageCaptcha();
      $captcha->uuid = $uuid;
      $captcha->save();
      return 0;
    } else {
      return 100006;
    }
  }

  /***auto route
   * name: create
   * type: admin
   * method: post
   */
  public function create()
  {
    $captcha = $this->generateCaptcha();
    $imageBase64 = $this->getImageBase64($captcha['image']);
    $time = time();
    $uuid = Str::orderedUuid();
    $code = mb_strtoupper($captcha['code']);
    $hash = Hash::make($code . ',Captcha,' . $uuid . ',' . $time);
    $ret = [
      'image' => $imageBase64,
      'hash' => $hash,
      'time' => $time,
      'uuid' => $uuid,
    ];
    if (env('APP_DEBUG')) {
      $ret['code'] = $code;
    }
    return Zi::echo($ret);
  }

  private function generateCaptcha()
  {
    $code = $this->generateRandomCode();
    $image = $this->generateImage($code);
    return [
      'code' => $code,
      'image' => $image
    ];
  }

  private function generateRandomCode()
  {
    $code = '';
    $characters = '34acdefhkmnprstwxyACDEFGHKMNPQRWXY';
    for ($i = 0; $i < 5; $i++) {
      $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
  }

  private function generateImage($code)
  {
    $draw_width = 130;
    $draw_height = 38;
    $image = imagecreate($draw_width, $draw_height);
    imagecolorallocate($image, 255, 255, 255);
    for ($i = 0; $i < 100; $i++) {
      $x = rand(0, $draw_width);
      $y = rand(0, $draw_height);
      $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
      imagesetpixel($image, $x, $y, $color);
    }
    for ($i = 0; $i < 5; $i++) {
      $x1 = rand(0, $draw_width);
      $y1 = rand(0, $draw_height);
      $x2 = rand(0, $draw_width);
      $y2 = rand(0, $draw_height);
      $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
      imageline($image, $x1, $y1, $x2, $y2, $color);
    }
    $font_file = 'assets/captcha.ttf';
    $original_path = Storage::disk('public')->path($font_file);
    $code_size = 26;
    for ($i = 0; $i < mb_strlen($code); $i++) {
      $c = mb_substr($code, $i, 1);
      $r = rand(-20, 20);
      $c_box = imagettfbbox($code_size, $r, $original_path, $c);
      imagettftext($image, $code_size, $r, 5 + ceil((23 - $c_box[2]) / 2) + (23 * $i), ceil(($draw_height - $c_box[5]) / 2),
        imagecolorallocate($image, rand(0, 100), rand(0, 100), rand(0, 100)), $original_path, $c);
    }
    ob_start();
    imagepng($image);
    $imageData = ob_get_clean();
    return $imageData;
  }

  private function getImageBase64($imageData)
  {
    $imageBase64 = base64_encode($imageData);
    return 'data:image/png;base64,' . $imageBase64;
  }
}
