<?php

namespace App\Http\Controllers;

use App\Lib\ZiQian;
use App\Models\QuickDatabase;
use Illuminate\Http\Request;
use App\Lib\Zi;
use App\Lib\Token;
use Illuminate\Support\Facades\DB;

class QuickDatabaseController extends Controller
{
  /***auto route
   * name: createData
   * type: admin
   * method: post
   * param: /{database}
   */
  public function createData($database)
  {
    $request = request();
    $data = $request->post('data');
    $quick_database = QuickDatabase::where('database', $database)->first();
    if (!$quick_database) Zi::eco(100001, ['数据库']);
    self::self_checkAuth($quick_database);
    self::self_checkRequest($quick_database, $data, 0);
    $id = DB::table($database)->insertGetId(array_merge($data, [
      'created_at' => ZiQian::date(),
      'updated_at' => ZiQian::date()
    ]));
    return Zi::cco($id);
  }

  /***auto route
   * name: updateData
   * type: admin
   * method: post
   * param: /{database}
   */
  public function updateData($database)
  {
    $request = request();
    $id = $request->post('id');
    $data = $request->post('data');
    $quick_database = QuickDatabase::where('database', $database)->first();
    if (!$quick_database) Zi::eco(100001, ['数据库']);
    self::self_checkAuth($quick_database);
    self::self_checkRequest($quick_database, $data, $id);
    $update = DB::table($database)->where('id', $id)->first();
    if (!$update) Zi::eco(100001, [$quick_database->name]);
    DB::table($database)->where('id', $id)->update(array_merge($data, [
      'updated_at' => ZiQian::date()
    ]));
    return Zi::uco($id);
  }

  /***auto route
   * name: deleteData
   * type: admin
   * method: post
   * param: /{database}
   */
  public function deleteData($database)
  {
    $request = request();
    $ids = $request->post('ids');
    $quick_database = QuickDatabase::where('database', $database)->first();
    if (!$quick_database) Zi::eco(100001, ['数据库']);
    self::self_checkAuth($quick_database);
    $list_config = json_decode($quick_database->list, true);
    if (isset($list_config['delete'])) {
      foreach ($ids as $id) {
        $db = DB::table($database);
        foreach ($list_config['delete']['where'] as $delete) {
          if ($delete[2] == '##DELETE-VALUE##') {
            $delete[2] = $id;
          }
          $db->where($delete[0], $delete[1], $delete[1] == 'like' ? '%' . $delete[2] . '%' : $delete[2]);
        }
        $delete_check = $db->count();
        if ($delete_check > 0) Zi::eco(100022, [$list_config['delete']['message']]);
      }
    }
    if ($quick_database->del === '') {
      DB::table($database)->whereIn('id', $ids)->delete();
    } else {
      $del_config = explode(':', $quick_database->del);
      DB::table($database)->whereIn('id', $ids)->update([
        $del_config[0] => $del_config[1],
        'updated_at' => ZiQian::date()
      ]);
    }
    return Zi::dco($ids);
  }

  /***auto route
   * name: listData
   * type: admin
   * method: post
   * param: /{database}
   * query: ?page={page}
   */
  public function listData($database)
  {
    $request = request();
    $search = $request->post('search');
    $quick_database = QuickDatabase::where('database', $database)->first();
    if (!$quick_database) Zi::eco(100001, ['数据库']);
    self::self_checkAuth($quick_database);
    $list_config = json_decode($quick_database->list, true);
    $data = DB::table($database)->select($list_config['select']);
    $search_rules = json_decode($quick_database->search, true);
    foreach ($search_rules as $key => $search_rule) {
      if ($search_rule['type'] === 'database_select') {
        $search_rules[$key]['type'] = 'select';
        $search_rules[$key]['select'] = self::self_databaseSelect($search_rule);
      }
    }
    foreach ($search_rules as $label => $search_rule) {
      if (isset($search[$label])) {
        $range_array = ['datetimerange', 'daterange', 'timerange'];
        if (in_array($search_rule['type'], $range_array)) {
          $search_type = ['>=', '<='];
          foreach ($search_type as $search_index => $search_item) {
            if (isset($search[$label][$search_index]) && !!$search[$label][$search_index]) {
              $value = $search[$label][$search_index];
              $where_array = $search_rule['where'];
              $data->where(function ($query) use ($value, $where_array, $search_item) {
                $index = 0;
                foreach ($where_array as $key => $where) {
                  if ($index == 0) {
                    $query->where($key, $search_item, $value);
                  } else {
                    $query->orWhere($key, $search_item, $value);
                  }
                  $index++;
                }
              });
            }
          }
        } else {
          if ($search[$label] != '') {
            $value = $search[$label];
            $where_array = $search_rule['where'];
            $data->where(function ($query) use ($value, $where_array) {
              $index = 0;
              foreach ($where_array as $key => $where) {
                if ($index == 0) {
                  $query->where($key, $where, $where == 'like' ? '%' . $value . '%' : $value);
                } else {
                  $query->orWhere($key, $where, $where == 'like' ? '%' . $value . '%' : $value);
                }
                $index++;
              }
            });
          }
        }
      }
    }
    if (isset($list_config['where'])) {
      foreach ($list_config['where'] as $where) {
        $data->where(function ($query) use ($where) {
          $query->where($where[0], $where[1], $where[1] == 'like' ? '%' . $where[2] . '%' : $where[2]);
        });
      }
    }
    foreach ($list_config['order'] as $order) {
      $data->orderBy($order['label'], $order['type']);
    }
    if ($list_config['page'] == 0) {
      $list = $data->get();
    } else {
      $list = $data->paginate($list_config['page']);
    }
    return Zi::echo([
      'list' => $list
    ]);
  }

  /***auto route
   * name: info
   * type: admin
   * method: post
   * param: /{database}
   */
  public function info($database)
  {
    $quick_database = QuickDatabase::where('database', $database)->first();
    if (!$quick_database) Zi::eco(100001, ['数据库']);
    self::self_checkAuth($quick_database);

    $form_groups = json_decode($quick_database->form, true);
    foreach ($form_groups as $key => $forms) {
      foreach ($forms as $k => $form) {
        if ($form['type'] === 'database_select') {
          $form_groups[$key][$k]['type'] = 'select';
          $form_groups[$key][$k]['select'] = self::self_databaseSelect($form);
        }
      }
    }
    $search_array = json_decode($quick_database->search, true);
    foreach ($search_array as $key => $search) {
      if ($search['type'] === 'database_select') {
        $search_array[$key]['type'] = 'select';
        $search_array[$key]['select'] = self::self_databaseSelect($search);
      }
    }

    return Zi::echo([
      'info' => [
        'list' => json_decode($quick_database->list, true),
        'search' => $search_array,
        'form' => $form_groups,
        'request' => json_decode($quick_database->request, true),
      ]
    ]);
  }

  public function self_databaseSelect($config)
  {
    $select = $config['select'];
    $database = $config['database'];
    $db = DB::table($database['name'])->select([
      DB::raw('`' . $database['value'] . '` as `value`'),
      DB::raw('`' . $database['label'] . '` as `label`')
    ]);
    foreach ($database['where'] as $where) {
      $db->where($where[0], $where[1], $where[1] == 'like' ? '%' . $where[2] . '%' : $where[2]);
    }
    foreach ($database['order'] as $order) {
      $db->orderBy($order['label'], $order['type']);
    }
    $list = $db->get()->toArray();
    return array_merge($select, $list);
  }

  public function self_checkRequest($quick_database, &$data, $id)
  {
    $rules = json_decode($quick_database->request, true);
    $form = json_decode($quick_database->form, true);
    $code = 100017;
    foreach ($rules as $label => $rule) {
      $form_index = 0;
      foreach ($form as $key => $form_item) {
        if (isset($form_item[$label])) {
          $form_index = $key;
          break;
        }
      }
      if (!isset($data[$label])) {
        $data[$label] = $form[$form_index][$label]['value'];
      } else if ((string)$data[$label] != '0') {
        if ($data[$label] == null || $data[$label] == '') {
          $data[$label] = $form[$form_index][$label]['value'];
        }
      }
      foreach ($rule['check'] as $check) {
        $message = $check['message'];
        if (isset($check['required']) && $check['required']) {
          if (!isset($data[$label])) Zi::eco($code, [$message]);
        }
        if (isset($data[$label])) {
          if (isset($check['min'])) {
            if (mb_strlen($data[$label]) < $check['min']) Zi::eco($code, [$message]);
          }

          if (isset($check['max'])) {
            if (mb_strlen($data[$label]) > $check['max']) Zi::eco($code, [$message]);
          }

          if (isset($check['type'])) {
            switch ($check['type']) {
              case 'ip':
                if (!filter_var($data[$label], FILTER_VALIDATE_IP)) Zi::eco($code, [$message]);
                break;
            }
          }

          if (isset($check['select'])) {
            if (!in_array($data[$label], $check['select'])) Zi::eco($code, [$message]);
          }

          if (isset($check['unique']) && !!$check['unique']) {
            $unique = DB::table($quick_database->database)
              ->where($label, $data[$label])
              ->where('id', '!=', $id)
              ->first();
            if (!!$unique) Zi::eco($code, [$message]);
          }

          if (isset($check['php'])) {
            $check_ret = true;
            eval($check['php']);
            if (!$check_ret) Zi::eco($code, [$message]);
          }
        }
      }
    }
  }

  public function self_checkAuth($quick_database)
  {
    Token::admin(json_decode($quick_database->auth, true), json_decode($quick_database->or_auth, true));
  }
}
