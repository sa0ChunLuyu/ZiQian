<?php

namespace App\Http\Controllers;

use App\Http\Request\EditAdmin;
use App\Http\Request\UpdateAdminInfo;
use App\Lib\Token;
use App\Models\Admin;
use App\Models\AdminAccount;
use App\Models\AdminToken;
use App\Models\Config;
use App\Models\IpPool;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Lib\Zi;
use App\Lib\ZiQian;
use Illuminate\Support\Str;

class AdminController extends Controller
{
  /***auto route
   * name: resetPassword
   * type: admin
   * method: post
   */
  public function resetPassword(Request $request)
  {
    Token::admin(['/admin/list']);
    $id = $request->post('id');
    $admin = Admin::where('id', $id)->where('del', 2)->first();
    if (!$admin) Zi::eco(100001, ['管理员']);
    $admin_account = AdminAccount::where('admin', $admin->id)->where('del', 2)->first();
    if (!$admin_account) Zi::eco(100001, ['管理员']);
    $password = Str::password(16);
    $admin->initial_password = 1;
    $admin->save();
    $admin_account->secret = bcrypt($password);
    $admin_account->save();
    return Zi::echo([
      'password' => $password
    ]);
  }

  /***auto route
   * name: create
   * type: admin
   * method: post
   */
  public function create(EditAdmin $request)
  {
    Token::admin(['/admin/list']);
    $account = $request->post('account');
    $admin_account = AdminAccount::where('account', $account)->where('type', 1)->where('del', 2)->first();
    if ($admin_account) Zi::eco(100021);
    $admin = new Admin();
    $admin->nickname = $request->post('nickname');
    $admin->avatar = $request->post('avatar') ?? '';
    $admin->admin_auth_group = $request->post('admin_auth_group');
    $admin->initial_password = $request->post('initial_password');
    $admin->status = $request->post('status');
    $admin->save();
    $admin_account = new AdminAccount();
    $admin_account->admin = $admin->id;
    $admin_account->account = $account;
    $admin_account->secret = bcrypt($request->post('password'));
    $admin_account->type = 1;
    $admin_account->save();
    return Zi::cco($admin->id);
  }

  /***auto route
   * name: update
   * type: admin
   * method: post
   */
  public function update(EditAdmin $request)
  {
    Token::admin(['/admin/list']);
    $id = $request->post('id');
    $account = $request->post('account');
    $admin_account = AdminAccount::where('admin', '!=', $id)->where('account', $account)->where('type', 1)->where('del', 2)->first();
    if ($admin_account) Zi::eco(100021);
    $admin = Admin::where('id', $id)->where('del', 2)->first();
    if (!$admin) Zi::eco(100001, ['管理员']);
    $admin_account = AdminAccount::where('admin', $id)->where('del', 2)->first();
    if (!$admin_account) Zi::eco(100001, ['管理员']);
    $admin->nickname = $request->post('nickname');
    $admin->avatar = $request->post('avatar') ?? '';
    $admin->admin_auth_group = $request->post('admin_auth_group');
    $admin->initial_password = $request->post('initial_password');
    $admin->status = $request->post('status');
    $admin->save();
    if ($admin_account->account != $account) {
      $admin_account->account = $request->post('account');
      $admin_account->save();
    }
    return Zi::uco($admin->id);
  }

  /***auto route
   * name: delete
   * type: admin
   * method: post
   */
  public function delete(Request $request)
  {
    Token::admin(['/admin/list']);
    $id = $request->post('id');
    $admin = Admin::where('id', $id)->where('del', 2)->first();
    if (!$admin) Zi::eco(100001, ['管理员']);
    $admin_account = AdminAccount::where('admin', $id)->where('del', 2)->first();
    if (!$admin_account) Zi::eco(100001, ['管理员']);
    $admin->del = 1;
    $admin->save();
    $admin_account->del = 1;
    $admin_account->save();
    return Zi::dco($admin->id);
  }

  /***auto route
   * name: list
   * type: admin
   * method: post
   * query: ?page={page}
   */
  public function list(Request $request)
  {
    Token::admin(['/admin/list']);
    $status = $request->post('status');
    $search = $request->post('search');
    $admin_auth_group = $request->post('admin_auth_group');
    $initial_password = $request->post('initial_password');
    $admin_list = Admin::select([
      DB::raw('admins.id as id'),
      DB::raw('admins.nickname as nickname'),
      DB::raw('admins.avatar as avatar'),
      DB::raw('admins.status as status'),
      DB::raw('admins.admin_auth_group as admin_auth_group'),
      DB::raw('admins.initial_password as initial_password'),
      DB::raw('admin_accounts.account as account'),
      DB::raw("IFNULL(admin_auth_groups.name,'') as admin_auth_group_name")
    ])
      ->leftJoin('admin_accounts', function (JoinClause $join) {
        $join->on('admin_accounts.admin', '=', 'admins.id')
          ->where('admin_accounts.type', '=', 1);
      })
      ->leftJoin('admin_auth_groups', 'admin_auth_groups.id', '=', 'admins.admin_auth_group')
      ->where(function ($query) use ($status) {
        if ($status != 0) $query->where('admins.status', $status);
      })
      ->where(function ($query) use ($admin_auth_group) {
        if ($admin_auth_group != 0) $query->where('admins.admin_auth_group', $admin_auth_group);
      })
      ->where(function ($query) use ($initial_password) {
        if ($initial_password != 0) $query->where('admins.initial_password', $initial_password);
      })
      ->where(function ($query) use ($search) {
        if ($search != '') $query->where('admins.nickname', 'like', "%$search%");
      })
      ->where('admins.del', 2)
      ->paginate(20);
    return Zi::echo([
      'list' => $admin_list
    ]);
  }

  /***auto route
   * name: login
   * type: admin
   * method: post
   */
  public function login(Request $request)
  {
    $captcha_type_config = Config::where('name', '后台图形验证')->first();
    if (!!$captcha_type_config) {
      if ($captcha_type_config->value == '1') {
        $hash = $request->post('hash');
        $code = $request->post('code');
        $time = $request->post('time');
        $uuid = $request->post('uuid');
        $captcha = null;
        switch ($captcha_type_config->value) {
          case '1':
            $captcha = new ImageCaptchaController();
            break;
        }
        $captcha_check = $captcha->check($hash, $code, $time, $uuid);
        if ($captcha_check != 0) Zi::eco($captcha_check);
      }
    }
    $account = $request->post('account');
    $password = $request->post('password');
    $type = 1;
    $admin_account = AdminAccount::where('account', $account)
      ->where('type', $type)
      ->where('del', 2)
      ->first();
    if (!$admin_account) Zi::eco(100007);
    if (!password_verify($password, $admin_account->secret)) Zi::eco(100007);
    $admin = Admin::where('id', $admin_account->admin)
      ->where('status', 1)
      ->where('del', 2)
      ->first();
    if (!$admin) Zi::eco(100003);
    Token::$info = $admin;
    Token::$type = 'admin';
    $token = $this->self_createToken($admin, $type);
    return Zi::echo([
      'token' => $token
    ]);
  }

  /***auto route
   * name: updateSelf
   * type: admin
   * method: post
   */
  public function updateSelf(UpdateAdminInfo $request)
  {
    Token::admin();
    $nickname = $request->post('nickname');
    $avatar = $request->post('avatar');
    Token::$info->nickname = $nickname;
    Token::$info->avatar = $avatar ?? '';
    Token::$info->save();
    return Zi::uco(Token::$info->id);
  }

  /***auto route
   * name: info
   * type: admin
   * method: post
   */
  public function info()
  {
    Token::admin();
    $token_ip_info = [
      'ip' => Token::$token->ip,
      'region' => Token::$token->region,
      'created_at' => date("Y-m-d H:i:s", strtotime(Token::$token->created_at)),
    ];
    $last_time_token = AdminToken::where('admin', Token::$info->id)
      ->where('id', '!=', Token::$token->id)
      ->orderBy('id', 'desc')->first();
    if ($last_time_token) {
      $last_time_token_ip_info = [
        'ip' => $last_time_token->ip,
        'region' => $last_time_token->region,
        'created_at' => date("Y-m-d H:i:s", strtotime($last_time_token->created_at)),
      ];
    } else {
      $last_time_token_ip_info = false;
    }
    return Zi::echo([
      'info' => [
        'id' => Token::$info->id,
        'nickname' => Token::$info->nickname,
        'avatar' => Token::$info->avatar,
        'initial_password' => Token::$info->initial_password,
        'token_ip_info' => $token_ip_info,
        'last_time_token_ip_info' => $last_time_token_ip_info,
      ]
    ]);
  }

  /***auto route
   * name: quit
   * type: admin
   * method: post
   */
  public function quit()
  {
    Token::admin_check();
    if (!!Token::$token) {
      Token::$token->del = 1;
      Token::$token->save();
    }
    return Zi::echo();
  }

  /***auto route
   * name: status
   * type: admin
   * method: post
   */
  public function status()
  {
    Token::admin();
    return Zi::echo();
  }

  public function self_createToken($info, $type = 1): string
  {
    if ($info->status != 1) Zi::eco(100003);
    if ($info->del != 2) Zi::eco(100003);
    $token_str = Str::orderedUuid();
    $token = new AdminToken();
    $token->admin = $info->id;
    $token->token = $token_str;
    $ip = ZiQian::ip();
    $token->ip = $ip;
    $token->region = '';
    $region_save_config = Config::where('name', '后台IP地区信息')->first();
    if ($region_save_config->value == '1') {
      if (filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip_pool = IpPool::where('ip', $ip)->orderBy('id', 'desc')->first();
        if (!!$ip_pool) {
          $token->region = $ip_pool->region;
        } else {
          $ip2region = new \Ip2Region();
          $record = $ip2region->simple($ip);
          if (!!$record) {
            $token->region = $record;
          }
        }
      }
    }
    // $type 1-密码登录
    $token->type = $type;
    $token->save();
    $only_one_config = Config::where('name', '后台账号单点登录')->first();
    if ($only_one_config->value == '1') {
      AdminToken::where('admin', $info->id)
        ->where('type', $type)
        ->where('del', 2)
        ->where('id', '!=', $token->id)
        ->update([
          'del' => 1
        ]);
    }
    return $token_str;
  }
}
