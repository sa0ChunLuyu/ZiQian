<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PushQuickDatabasesData extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    $data = [[
      'name' => 'IP解析库',
      'auth' => '["/config/ip"]',
      'or_auth' => '[]',
      'database' => 'ip_pools',
      'del' => '',
      'list' => '{"page":20,"multiple":true,"select":["*"],"button":["create","update","delete","export"],"order":[{"label":"id","type":"desc"}],"form":{"width":400,"span":[400]},"export":[{"value":"id","label":"序号","width":20},{"value":"ip","label":"IP地址","width":50},{"value":"region","label":"解析地址","width":0},{"value":"created_at","label":"创建时间","width":50}],"table":[{"value":"id","label":"序号","type":"string","width":60},{"value":"ip","label":"IP地址","type":"string","width":200},{"value":"region","label":"解析地址","type":"string","width":0},{"value":"created_at","label":"创建时间","type":"string","width":200}]}',
      'search' => '{"search_str":{"type":"string","label":"搜索","value":"","placeholder":"请输入IP地址/解析地址","where":{"ip":"=","region":"="}},"time":{"type":"datetimerange","label":"创建时间","value":["",""],"placeholder":"","where":{"created_at":""}}}',
      'form' => '[{"ip":{"label":"IP地址","type":"string","value":"","placeholder":"请输入IP地址"},"region":{"label":"解析地址","type":"string","value":"","placeholder":"请输入IP地址"}}]',
      'request' => '{"ip":{"check":[{"required":true,"message":"请输入IP地址"},{"min":7,"max":15,"message":"IP地址长度应在6-15位字符之间"},{"type":"ip","message":"请填写正确IP地址"}]},"region":{"check":[{"required":true,"message":"请输入解析地址"},{"min":1,"max":50,"message":"解析地址长度应在1-50位字符之间"}]}}',
    ],[
      'name' => '权限管理',
      'auth' => '["/admin/auth"]',
      'or_auth' => '[]',
      'database' => 'admin_auth_groups',
      'del' => 'del:1',
      'list' => '{"page":0,"multiple":true,"select":["*"],"button":["create","update","delete"],"order":[{"label":"id","type":"desc"}],"form":{"width":400,"span":[400]},"delete":{"message":"该权限被人员绑定，不能删除","database":"admins","where":[["admin_auth_group","=","##DELETE-VALUE##"],["del","=",2]]},"table":[{"value":"id","label":"序号","type":"string","width":60},{"value":"name","label":"分组名称","type":"string","width":200},{"value":"admin_auths","label":"权限","type":"json_array_count","show":"{value}个","width":60},{"value":"remark","label":"备注","type":"string","width":0},{"value":"status","label":"状态","type":"select","select":[{"value":1,"label":"可用"},{"value":2,"label":"禁用"}],"width":60},{"value":"created_at","label":"创建时间","type":"string","width":200}]}',
      'search' => '{}',
      'form' => '[{"name":{"label":"分组名称","type":"string","value":"","placeholder":"请输入分组名称"},"remark":{"label":"备注","type":"string","value":"","placeholder":"请输入备注"},"status":{"label":"状态","type":"select","value":1,"select":[{"value":1,"label":"可用"},{"value":2,"label":"禁用"}],"placeholder":"请选择状态"}}]',
      'request' => '{"name":{"check":[{"required":true,"message":"请输入分组名称"},{"min":1,"max":20,"message":"IP地址长度应在1-20位字符之间"}]},"remark":{"check":[{"min":0,"max":100,"message":"备注长度应在100位字符以内"}]},"status":{"check":[{"select":[1,2],"message":"请输入正确的数值"}]}}',
    ],[
      'name' => '参数配置',
      'auth' => '["/config/config"]',
      'or_auth' => '[]',
      'database' => 'configs',
      'del' => '',
      'list' => '{"page":0,"multiple":false,"select":["*"],"button":["create","update","delete"],"order":[{"label":"id","type":"asc"}],"form":{"width":1200,"span":[250,900]},"table":[{"value":"id","label":"序号","type":"string","width":60},{"value":"name","label":"配置名称","type":"string","width":200},{"value":"value","label":"配置","type":"bind:type","tooltip":11,"width":200},{"value":"type","label":"状态","type":"select","select":[{"value":"string","label":"文字"},{"value":"textarea","label":"文本框"},{"value":"image","label":"图片"},{"value":"stringArray","label":"文字数组"},{"value":"imageArray","label":"图片数组"},{"value":"json","label":"JSON"},{"value":"richText","label":"富文本"},{"value":"switch","label":"开关"},{"value":"color","label":"颜色"}],"width":100},{"value":"client","label":"获取类型","type":"select","select":[{"value":0,"label":"公共"},{"value":1,"label":"后台"}],"width":90},{"value":"login","label":"登录类型","type":"select","select":[{"value":1,"label":"登录获取","default":""},{"value":2,"label":"开放获取","default":""}],"width":90},{"value":"desc","label":"备注","type":"string","width":0}]}',
      'search' => '{}',
      'form' => '[{"name":{"label":"配置名称","type":"string","value":"","placeholder":"请输入配置名称"},"type":{"label":"配置类型","type":"select","value":"string","change":["default:1:value"],"select":[{"value":"string","label":"文字","default":""},{"value":"textarea","label":"文本框","default":""},{"value":"image","label":"图片","default":""},{"value":"stringArray","label":"文字数组","default":[]},{"value":"imageArray","label":"图片数组","default":[]},{"value":"json","label":"JSON","default":{}},{"value":"richText","label":"富文本","default":""},{"value":"switch","label":"开关","default":true},{"value":"color","label":"颜色","default":"#000000"}],"placeholder":"请选择配置类型"},"client":{"label":"获取类型","type":"select","value":0,"select":[{"value":0,"label":"公共"},{"value":1,"label":"后台"}],"placeholder":"请选择获取类型"},"login":{"label":"登录类型","type":"select","value":2,"select":[{"value":1,"label":"登录获取"},{"value":2,"label":"开放获取"}],"placeholder":"请选择登录类型"},"desc":{"label":"备注","type":"string","value":"","placeholder":"请输入备注"}},{"value":{"label":"配置","type":"bind:type","value":"","placeholder":""}}]',
      'request' => '{"name":{"check":[{"required":true,"message":"请输入配置名称"},{"unique":true,"message":"配置名称已存在"},{"min":1,"max":20,"message":"IP地址长度应在1-20位字符之间"}]},"type":{"check":[{"select":["string","textarea","image","stringArray","imageArray","json","richText","switch","color"],"message":"请输入正确的数值"}]},"client":{"check":[{"select":[0,1],"message":"请输入正确的数值"}]},"login":{"check":[{"select":[1,2],"message":"请输入正确的数值"}]},"desc":{"check":[{"min":0,"max":100,"message":"备注长度应在100位字符以内"}]}}',
    ],[
      'name' => '路由配置',
      'auth' => '["/config/router"]',
      'or_auth' => '[]',
      'database' => 'admin_auths',
      'del' => 'del:1',
      'list' => '{"page":0,"multiple":false,"select":["*"],"button":["create","update","delete"],"order":[{"label":"order","type":"desc"},{"label":"id","type":"asc"}],"delete":{"message":"该分类下有页面/接口存在，不能删除","where":[["pid","=","##DELETE-VALUE##"],["del","=",2]]},"where":[["del","=",2]],"form":{"width":800,"span":[375,375]},"table":[{"value":"id","label":"序号","type":"string","width":60},{"value":"title","label":"名称","type":"string","width":200},{"value":"name","label":"路由","type":"string","width":200},{"value":"icon","label":"图标","type":"icon","width":70},{"value":"type","label":"获取类型","type":"select","select":[{"value":1,"label":"分组"},{"value":2,"label":"页面/接口"}],"width":90},{"value":"check","label":"验证类型","type":"select","select":[{"value":1,"label":"需要验证"},{"value":2,"label":"不需要验证"}],"width":100},{"value":"show","label":"显示/隐藏","type":"select","select":[{"value":1,"label":"显示"},{"value":2,"label":"不显示"}],"width":90},{"value":"status","label":"状态","type":"select","select":[{"value":1,"label":"正常"},{"value":2,"label":"禁用"}],"width":90},{"value":"message","label":"验证失败提示","type":"string","width":0},{"value":"order","label":"排序","type":"string","width":60}]}',
      'search' => '{"pid":{"type":"database_select","label":"分组","value":0,"placeholder":"请选择分组","database":{"name":"admin_auths","value":"id","label":"title","where":[["pid","=","0"],["del","=","2"]],"order":[{"label":"order","type":"desc"}]},"select":[{"value":0,"label":"根目录"}],"where":{"pid":"="}}}',
      'form' => '[{"title":{"label":"名称","type":"string","value":"","placeholder":"请输入名称"},"icon":{"label":"图标","type":"icon","value":"","placeholder":"请选择图标"},"pid":{"label":"分组","type":"database_select","value":"query:pid:number","placeholder":"请选择分组","database":{"name":"admin_auths","value":"id","label":"title","where":[["pid","=","0"],["del","=","2"]],"order":[{"label":"order","type":"desc"}]},"select":[{"value":0,"label":"根目录"}]},"type":{"label":"获取类型","type":"select","value":1,"select":[{"value":1,"label":"分组"},{"value":2,"label":"页面/接口"}],"placeholder":"请选择获取类型"},"check":{"label":"验证类型","type":"select","value":2,"select":[{"value":1,"label":"需要验证"},{"value":2,"label":"不需要验证"}],"placeholder":"请选择验证类型"}},{"name":{"label":"路由","type":"string","value":"","placeholder":"请输入路由"},"show":{"label":"显示/隐藏","type":"select","value":1,"select":[{"value":1,"label":"显示"},{"value":2,"label":"不显示"}],"placeholder":"请选择显示/隐藏"},"status":{"label":"状态","type":"select","value":1,"select":[{"value":1,"label":"正常"},{"value":2,"label":"禁用"}],"placeholder":"请选择状态"},"message":{"label":"验证失败提示","type":"string","value":"","placeholder":"请输入验证失败提示"},"order":{"label":"排序","type":"number","value":0,"step":1,"min":0,"max":999,"placeholder":"请输入排序"}}]',
      'request' => '{"name":{"check":[{"required":true,"message":"请输入路由"},{"unique":true,"message":"路由已存在"},{"min":1,"max":200,"message":"路由长度应在1-200位字符之间"}]},"title":{"check":[{"required":true,"message":"请输入名称"},{"min":1,"max":20,"message":"名称长度应在1-20位字符之间"}]},"icon":{"check":[{"min":0,"max":100,"message":"图标长度应在100位字符以内"}]},"pid":{"check":[{"php":"if($data[\'type\']==1&&$data[\'pid\']!=0){$check_ret = false;}","message":"分组只能创建在根目录下"},{"php":"if($data[\'type\']==2&&$data[\'pid\']==0){$check_ret = false;}","message":"页面/接口只能创建在分组下"}]},"type":{"check":[{"select":[1,2],"message":"请输入正确的数值"}]},"check":{"check":[{"select":[1,2],"message":"请输入正确的数值"}]},"show":{"check":[{"select":[1,2],"message":"请输入正确的数值"}]},"status":{"check":[{"select":[1,2],"message":"请输入正确的数值"}]},"message":{"check":[{"min":0,"max":50,"message":"验证失败提示信息长度应在50位字符以内"}]}}',
    ],[
      'name' => '上传管理',
      'auth' => '["/config/upload"]',
      'or_auth' => '[]',
      'database' => 'uploads',
      'del' => '',
      'list' => '{"page":20,"multiple":false,"select":["*"],"button":[],"order":[{"label":"id","type":"desc"}],"form":{"width":0,"span":[]},"table":[{"value":[{"value":{"value":"url","type":"ext"},"label":"文件预览","type":"file","rowspan":3,"width":80},{"value":"uuid","label":"UUID","type":"string","width":150},{"value":"name","label":"文件名","type":"string","width":150},{"value":"ext","label":"文件类型","type":"string","width":""},{"value":[{"value":"copy:image:url","button":{"type":"primary","size":"small"},"label":"复制地址","type":"button"},{"value":"url","label":"文件地址","type":"string"}],"label":"文件地址","type":"inline","span":2,"width":150},{"value":"size","label":"文件大小","type":"string","show":"{value}MB","width":""},{"value":"from","label":"文件来源","type":"string","width":150},{"value":"md5","label":"MD5","type":"string","width":150},{"value":"created_at","label":"上传时间","type":"string","width":""}],"label":"文件信息","type":"desc","column":4,"width":0}]}',
      'search' => '{"search_str":{"type":"string","label":"搜索","value":"","placeholder":"请输入","where":{"uuid":"=","name":"=","md5":"="}},"time":{"type":"datetimerange","label":"创建时间","value":["",""],"placeholder":"","where":{"created_at":""}},"ext":{"type":"database_select","label":"文件类型","value":"","placeholder":"请选择文件类型","database":{"name":"uploads","value":"ext","label":"ext","group":["ext"],"order":[{"label":"id","type":"desc"}]},"select":[{"value":"","label":"全部"}],"where":{"ext":"="}},"from":{"type":"database_select","label":"来源","value":"","placeholder":"请选择来源","database":{"name":"uploads","value":"from","label":"from","group":["from"],"order":[{"label":"id","type":"desc"}]},"select":[{"value":"","label":"全部"}],"where":{"from":"="}}}',
      'form' => '[]',
      'request' => '{}',
    ]];
    foreach ($data as $datum) {
      $qd = new App\Models\QuickDatabase();
      $qd->name = $datum['name'];
      $qd->auth = $datum['auth'];
      $qd->or_auth = $datum['or_auth'];
      $qd->database = $datum['database'];
      $qd->del = $datum['del'];
      $qd->list = $datum['list'];
      $qd->search = $datum['search'];
      $qd->form = $datum['form'];
      $qd->request = $datum['request'];
      $qd->save();
    }
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    //
  }
}
