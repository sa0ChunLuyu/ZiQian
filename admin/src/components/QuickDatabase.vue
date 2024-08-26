<script setup>
/**
 * name：QuickDatabase
 * user：sa0ChunLuyu
 * date：2024年8月26日 10:43:09
 */
import {$api, $image, $response} from '~/api'
import {onBeforeRouteUpdate} from "vue-router";
import $router from "~/router";

const $props = defineProps({
  database: {
    type: String,
    default: ''
  }
})
const database_info = ref(false)
const getDatabaseInfo = async () => {
  if (!database_info.value) {
    const response = await $api('AdminQuickDatabaseInfo', {
      database: $props.database
    })
    $response(response, () => {
      database_info.value = response.data.info
      setSearchForm()
    })
  } else {
    setSearchForm()
  }
}
const search_form = ref({})
const search_default = ref({})
const setSearchForm = () => {
  let sf = {}
  let po = JSON.parse(JSON.stringify(page_options.value.s))
  for (let i in database_info.value.search) {
    sf[i] = {
      ...database_info.value.search[i],
      value: !!po[i] ? po[i] : database_info.value.search[i].value
    }
    search_default.value[i] = database_info.value.search[i].value
  }
  search_form.value = sf
  getDataList()
}
const table_list = ref([])
const list_total = ref(0)
const getDataList = async () => {
  let s = {}
  for (let i in search_form.value) {
    s[i] = search_form.value[i].value
  }
  let q = {
    search: s,
  }
  if (!!database_info.value.list.page) {
    q.page = page_options.value.page
  }
  const response = await $api('AdminQuickDatabaseListData', {
    database: $props.database,
    ...q,
  })
  $response(response, () => {
    if (!!database_info.value.list.page) {
      table_list.value = response.data.list.data.map((item) => {
        return {
          ...item,
          EDIT_ACTIVE: false
        }
      })
      list_total.value = response.data.list.total
    } else {
      table_list.value = response.data.list.map((item) => {
        return {
          ...item,
          EDIT_ACTIVE: false
        }
      })
      list_total.value = 0
    }
  })
}
const routerChange = (query) => {
  page_options.value = {
    page: ('page' in query && !!Number(query.page)) ? Number(query.page) : default_page_options.page,
    s: ('s' in query && !!query.s) ? JSON.parse(query.s) : default_page_options.s
  }
  getDatabaseInfo()
}
const default_page_options = {
  s: '{}',
  page: 1
}
const searchClick = (page = 1) => {
  let s = {}
  for (let i in search_form.value) {
    s[i] = search_form.value[i].value
  }
  let q = {
    s: JSON.stringify(s),
  }
  if (!!database_info.value.list.page) {
    q.page = page
  }
  $router.push({
    query: q
  })
}
const searchClearClick = () => {
  let q = {
    s: JSON.stringify(search_default.value),
  }
  if (!!database_info.value.list.page) {
    q.page = 1
  }
  $router.push({
    query: q
  })
}
const table_list_active = computed(() => {
  return table_list.value.filter((item) => {
    return item.EDIT_ACTIVE
  })
})
const page_options = ref(default_page_options)
onBeforeRouteUpdate((to) => {
  routerChange(to.query)
})
const edit_show = ref(false)
const edit_data = ref({
  id: 0
})
const setEditData = (info) => {
  let e = {
    id: info.id
  }
  for (let i in database_info.value.form) {
    e[i] = i in info ? info[i] : database_info.value.form[i].value
  }
  edit_data.value = e
}
const createClick = () => {
  setEditData({id: 0})
  edit_show.value = true
}
const updateClick = () => {
  setEditData(JSON.parse(JSON.stringify(table_list_active.value[0])))
  edit_show.value = true
}
const deleteClick = () => {
  window.$box.confirm(
      '是否确认删除选中数据？',
      '提示',
      {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning',
      }
  ).then(() => {
    deleteData()
  }).catch(() => {
  })
}

const deleteData = async () => {
  const response = await $api('AdminQuickDatabaseDeleteData', {
    database: $props.database,
    ids: table_list_active.value.map((item) => {
      return item.id
    })
  })
  $response(response, () => {
    window.$message().success('删除成功')
    getDataList()
  })
}

const editDone = async () => {
  let check = checkForm()
  if (!!check) {
    window.$message().error(check)
  } else {
    const response = await $api(edit_data.value.id === 0
        ? 'AdminQuickDatabaseCreateData'
        : 'AdminQuickDatabaseUpdateData', {
      database: $props.database,
      id: edit_data.value.id,
      data: edit_data.value
    })
    $response(response, () => {
      window.$message().success(response.message)
      edit_show.value = false
      getDataList()
    })
  }
}

const checkForm = () => {
  for (let i in database_info.value.request) {
    let check = database_info.value.request[i].check
    for (let ii in check) {
      let rule = check[ii]
      if (rule.required && !edit_data.value[i]) {
        return rule.message
      }
      if (!!edit_data.value[i]) {
        if (!!rule.mix) {
          if (edit_data.value[i].length < rule.mix) {
            return rule.message
          }
        }
        if (!!rule.max) {
          if (edit_data.value[i].length > rule.max) {
            return rule.message
          }
        }
      }
    }
  }
  return ''
}

const valueShow = (data, column) => {
  switch (column.type) {
    case 'select':
      for (let i in column.select) {
        if (column.select[i].value === data) {
          data = column.select[i].label
          break
        }
      }
      break
    case 'json_array_count':
      data = JSON.parse(data).length
      break
  }
  if ('show' in column && !!column.show) {
    data = column.show.replace('{value}', data)
  }
  return data
}

defineExpose({
  table_list_active,
  getDataList
})

onMounted(() => {
  routerChange($router.currentRoute.value.query)
})
</script>
<template>
  <el-dialog v-model="edit_show" :title="edit_data.id === 0 ? '新建' : '编辑'" width="500">
    <div>
      <el-form label-position="top">
        <el-form-item v-for="(i,k) in database_info.form" :key="k" :label="i.label">
          <template v-if="i.type === 'select'">
            <el-select v-model="edit_data[k]" :placeholder="i.placeholder">
              <el-option v-for="(ii,ik) in i.select" :key="ik" :label="ii.label"
                         :value="ii.value"></el-option>
            </el-select>
          </template>
          <template v-else>
            <el-input v-model="edit_data[k]" :placeholder="i.placeholder"></el-input>
          </template>
        </el-form-item>
      </el-form>
    </div>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="edit_show = false">取消</el-button>
        <el-button type="primary" @click="editDone()">确认</el-button>
      </div>
    </template>
  </el-dialog>

  <div>
    <el-form v-if="JSON.stringify(search_form) !== '{}'" :inline="true">
      <el-form-item v-for="(i,k) in search_form" :key="k" :label="i.label">
        <template v-if="i.type === 'string'">
          <el-input v-model="search_form[k].value" :placeholder="i.placeholder"></el-input>
        </template>
        <template v-else-if="i.type === 'datetimerange'">
          <el-date-picker v-model="search_form[k].value" type="datetimerange" format="YYYY-MM-DD HH:mm:ss"
                          value-format="YYYY-MM-DD HH:mm:ss"/>
        </template>
      </el-form-item>
      <el-form-item>
        <el-button @click="searchClick()" type="primary">搜索</el-button>
        <el-button @click="searchClearClick()">清空</el-button>
      </el-form-item>
    </el-form>
    <div v-if="!!database_info">
      <div class="table_button_wrapper">
        <div class="table_button_group_wrapper">
          <el-button v-if="database_info.list.button.includes('create')" @click="createClick()" type="primary">
            添加数据
          </el-button>
          <el-button v-if="database_info.list.button.includes('update')" :disabled="table_list_active.length !== 1"
                     @click="updateClick()" type="primary">修改
          </el-button>
          <el-button v-if="database_info.list.button.includes('delete')" :disabled="table_list_active.length === 0"
                     @click="deleteClick()" type="danger">删除
          </el-button>
          <div class="ml-3">
            <slot></slot>
          </div>
        </div>
        <div class="table_button_group_wrapper">
          <el-button v-if="database_info.list.button.includes('import')" disabled type="success">导入数据</el-button>
          <el-button v-if="database_info.list.button.includes('export')" disabled type="warning">导出</el-button>
        </div>
      </div>
      <el-table mt-2 border :data="table_list" style="width: 100%">
        <el-table-column label="" width="40">
          <template #default="scope">
            <el-checkbox v-model="table_list[scope.$index].EDIT_ACTIVE"></el-checkbox>
          </template>
        </el-table-column>
        <el-table-column v-for="(i,k) in database_info.list.table" :key="k" :label="i.label" :width="i.width">
          <template #default="scope">
            <div v-if="!!scope.row[i.value]">{{ valueShow(scope.row[i.value], i) }}</div>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination v-if="!!list_total" :current-page="page_options.page" mt-2 background layout="prev, pager, next"
                     :page-count="Math.ceil(list_total/database_info.list.page)" @update:current-page="searchClick"/>
    </div>
  </div>
</template>
<style scoped>
.table_button_wrapper {
  display: flex;
  align-items: center;
  justify-content: space-between;

  .table_button_group_wrapper {
    display: flex;
    align-items: center;
  }
}
</style>

