<?php

namespace App\Http\Controllers;

use App\Http\Request\ChangeAdminPassword;
use App\Models\AdminAccount;
use App\Lib\Token;
use App\Lib\Zi;

class AdminAccountController extends Controller
{
  /***auto route
   * name: changePassword
   * type: admin
   * method: post
   */
  public function changePassword(ChangeAdminPassword $request)
  {
    Token::admin();
    $hash = $request->post('hash');
    $code = $request->post('code');
    $time = $request->post('time');
    $uuid = $request->post('uuid');
    $captcha = new ImageCaptchaController();
    $captcha_check = $captcha->check($hash, $code, $time, $uuid);
    if ($captcha_check != 0) Zi::eco($captcha_check);
    $old_password = $request->post('old_password');
    $password = $request->post('password');
    $admin_account = AdminAccount::where('admin', Token::$info->id)
      ->where('type', 1)
      ->where('del', 2)
      ->first();
    if (!$admin_account) Zi::eco(100001, ['账号']);
    if (!password_verify($old_password, $admin_account->secret)) Zi::eco(100008);
    if ($old_password == $password) Zi::eco(100009);
    $admin_account->secret = bcrypt($password);
    $admin_account->save();
    if (Token::$info->initial_password == 1) {
      Token::$info->initial_password = 2;
      Token::$info->save();
    }
    return Zi::uco(Token::$info->init_password);
  }
}
