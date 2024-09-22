<?php

namespace App\Http\Controllers;

use App\Models\AdminAuthGroup;
use Illuminate\Http\Request;
use App\Lib\Token;
use App\Lib\Zi;

class AdminAuthGroupController extends Controller
{
  /***auto route
   * name: update
   * type: admin
   * method: post
   */
  public function update(Request $request)
  {
    Token::admin(['/admin/auth']);
    $admin_auth_group = AdminAuthGroup::where('id', $request->post('id'))
      ->where('del', 2)->first();
    if (!$admin_auth_group) Zi::eco(100001, ['权限组']);
    $admin_auth_group->admin_auths = $request->post('admin_auths');
    $admin_auth_group->save();
    return Zi::uco($admin_auth_group->id);
  }

  /***auto route
   * name: select
   * type: admin
   * method: post
   */
  public function select()
  {
    Token::admin();
    $admin_auth_group = AdminAuthGroup::where('del', 2)->get();
    return Zi::echo([
      'list' => $admin_auth_group
    ]);
  }
}
