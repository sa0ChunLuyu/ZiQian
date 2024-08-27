<script setup>
/**
 * name：QuickDatabase
 * user：sa0ChunLuyu
 * date：2024年8月26日 10:43:09
 */
import {$api, $base64, $image, $response} from '~/api'
import {onBeforeRouteUpdate} from "vue-router";
import $router from "~/router";
import JsonEditorVue from 'json-editor-vue3'
import VueJsonPretty from 'vue-json-pretty'

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
const last_page = ref(0)
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
      last_page.value = response.data.list.last_page
    } else {
      table_list.value = response.data.list.map((item) => {
        return {
          ...item,
          EDIT_ACTIVE: false
        }
      })
      last_page.value = 0
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
  id: 0,
  form: []
})
const setEditData = (info) => {
  let e = {
    id: info.id,
    form: []
  }
  for (let i in database_info.value.form) {
    let form = {}
    for (let ii in database_info.value.form[i]) {
      if (ii in info) {
        if (json_array.includes(formType(database_info.value.form[i][ii].type, e))) {
          form[ii] = JSON.parse(info[ii])
        } else {
          form[ii] = info[ii]
        }
      } else {
        form[ii] = database_info.value.form[i][ii].value
      }
    }
    e.form.push(form)
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
const json_array = [
  'imageArray',
  'stringArray',
  'json'
]
const editDone = async () => {
  let check = checkForm()
  if (!!check) {
    window.$message().error(check)
  } else {
    let data = {}
    for (let i in edit_data.value.form) {
      data = {
        ...data,
        ...edit_data.value.form[i]
      }
    }
    for (let i in database_info.value.form) {
      for (let ii in database_info.value.form[i]) {
        let type = formType(database_info.value.form[i][ii].type)
        if (json_array.includes(type)) {
          data[ii] = JSON.stringify(data[ii])
        } else if (type === 'richText') {
          data[ii] = rich_text_ref.value[ii].getContent()
        }
      }
    }
    const response = await $api(edit_data.value.id === 0
        ? 'AdminQuickDatabaseCreateData'
        : 'AdminQuickDatabaseUpdateData', {
      database: $props.database,
      id: edit_data.value.id,
      data
    })
    $response(response, () => {
      window.$message().success(response.message)
      edit_show.value = false
      getDataList()
    })
  }
}

const checkForm = () => {
  let data = {}
  for (let i in edit_data.value.form) {
    data = {
      ...data,
      ...edit_data.value.form[i]
    }
  }
  for (let i in database_info.value.request) {
    let check = database_info.value.request[i].check
    for (let ii in check) {
      let rule = check[ii]
      if (rule.required && !data[i]) {
        return rule.message
      }
      if (!!data[i]) {
        if (!!rule.mix) {
          if (data[i].length < rule.mix) {
            return rule.message
          }
        }
        if (!!rule.max) {
          if (data[i].length > rule.max) {
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

const editActiveChange = (active_index) => {
  if (!database_info.value.list.multiple) {
    table_list.value.forEach((item, index) => {
      if (index !== active_index) {
        item.EDIT_ACTIVE = false
      }
    })
  }
}

const formSelectChange = (e, index, label) => {
  let config = database_info.value.form[index][label]
  if ('change' in config && !!config.change) {
    for (let i in config.change) {
      let change = config.change[i].split(':')
      let form_config = database_info.value.form[index][label]
      for (let ii in form_config.select) {
        if (form_config.select[ii].value === e) {
          edit_data.value.form[Number(change[1])][change[2]] = form_config.select[ii][change[0]]
          break
        }
      }
    }
  }
}
const formType = (type, e = false, table = false) => {
  let value = type
  if (type.includes('bind:')) {
    for (let i in database_info.value.form) {
      for (let ii in database_info.value.form[i]) {
        if (ii === type.replace('bind:', '')) {
          if (!!table) {
            return e[ii]
          } else {
            return !!e ? e.form[i][ii] : edit_data.value.form[i][ii]
          }
        }
      }
    }
  }
  return value
}

const fileChange = async (e, index, label, key) => {
  if (e.size > 1024 * 1024 * 2) return window.$message().error('图片大小不能超过2M')
  await UploadImage(await $base64(e.raw), index, label, key)
}
const UploadImage = async (base64, index, label, key) => {
  const response = await $api('AdminUploadImage', {
    base64
  })
  $response(response, () => {
    switch (key) {
      case -2:
        edit_data.value.form[index][label] = response.data.url
        break
      case -1:
        edit_data.value.form[index][label].push(response.data.url)
        break
      default:
        edit_data.value.form[index][label][key] = response.data.url
    }
  })
}

const predefine_config = [
  '#ff4500',
  '#ff8c00',
  '#ffd700',
  '#90ee90',
  '#00ced1',
  '#1e90ff',
  '#c71585',
  'rgba(255, 69, 0, 0.68)',
  'rgb(255, 120, 0)',
  'hsv(51, 100, 98)',
  'hsva(120, 40, 94, 0.5)',
  'hsl(181, 100%, 37%)',
  'hsla(209, 100%, 56%, 0.73)',
  '#c7158577',
]

const rich_text_ref = ref({})
const richTextRef = (e, label) => {
  rich_text_ref.value[label] = e
}

const string_array_input = ref('')
const stringArrayCreateClick = (k, ik) => {
  edit_data.value.form[k][ik].push(JSON.parse(JSON.stringify(string_array_input.value)))
  string_array_input.value = ''
}

const stringArrayDeleteClick = (k, ik, iik) => {
  edit_data.value.form[k][ik].splice(iik, 1)
}
const imageDeleteClick = (k, ik, iik) => {
  switch (iik) {
    case -2:
      edit_data.value.form[k][ik] = ''
      break
    default:
      edit_data.value.form[k][ik].splice(iik, 1)
  }
}

const rich_text = ref('')
const rich_text_show = ref(false)
const richTextShow = (value) => {
  rich_text.value = value
  rich_text_show.value = true
}

const json = ref({})
const json_show = ref(false)
const jsonShow = (value) => {
  json.value = JSON.parse(value)
  json_show.value = true
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
  <el-dialog v-model="rich_text_show" title="富文本"
             :width="1000">
    <el-scrollbar height="300px">
      <div v-html="rich_text"></div>
    </el-scrollbar>
  </el-dialog>
  <el-dialog v-model="json_show" title="JSON"
             :width="1000">
    <el-scrollbar height="300px">
      <VueJsonPretty :data="json"></VueJsonPretty>
    </el-scrollbar>
  </el-dialog>
  <el-dialog v-if="!!database_info" v-model="edit_show" :title="edit_data.id === 0 ? '新建' : '编辑'"
             :width="database_info.list.form.width">
    <div v-if="!!edit_show" class="form_col_wrapper">
      <div v-for="(i,k) in database_info.form" :key="k" :style="{
        width: `${database_info.list.form.span[k]}px`
      }">
        <el-form label-position="top">
          <el-form-item v-for="(ii,ik) in database_info.form[k]" :key="ik" :label="ii.label">
            <template v-if="formType(ii.type) === 'select'">
              <el-select @change="(e)=>{formSelectChange(e,k,ik)}" v-model="edit_data.form[k][ik]"
                         :placeholder="ii.placeholder">
                <el-option v-for="(iii,iik) in ii.select" :key="iik" :label="iii.label"
                           :value="iii.value"></el-option>
              </el-select>
            </template>
            <template v-else-if="formType(ii.type) === 'image'">
              <div class="form_image_wrapper">
                <div v-if="!!edit_data.form[k][ik]" class="form_image_delete_wrapper">
                  <el-button @click="imageDeleteClick(k, ik, -2)" type="danger" size="small">
                    <Icon type="delete" :size="12"></Icon>
                  </el-button>
                </div>
                <el-upload :auto-upload="false" :show-file-list="false" @change="(e)=>{fileChange(e,k,ik,-2)}">
                  <el-image class="form_image_show_wrapper" v-if="!!edit_data.form[k][ik]"
                            :src="$image(edit_data.form[k][ik])" fit="contain"></el-image>
                  <div v-else class="form_image_empty_wrapper">上传图片</div>
                </el-upload>
              </div>
            </template>
            <template v-else-if="formType(ii.type) === 'textarea'">
              <el-input type="textarea" v-model="edit_data.form[k][ik]" :placeholder="ii.placeholder"></el-input>
            </template>
            <template v-else-if="formType(ii.type) === 'stringArray'">
              <div w-full>
                <div v-for="(iii,iik) in edit_data.form[k][ik]" class="form_string_array_wrapper mb-2">
                  <el-input class="form_string_array_input_wrapper" v-model="edit_data.form[k][ik][iik]"
                            :placeholder="ii.placeholder"></el-input>
                  <el-button @click="stringArrayDeleteClick(k, ik, iik)" text>
                    <Icon type="delete"></Icon>
                  </el-button>
                </div>
                <div class="form_string_array_wrapper">
                  <el-input class="form_string_array_input_wrapper" v-model="string_array_input"
                            :placeholder="ii.placeholder"></el-input>
                  <el-button @click="stringArrayCreateClick(k, ik)" text>
                    <Icon type="plus"></Icon>
                  </el-button>
                </div>
              </div>
            </template>
            <template v-else-if="formType(ii.type) === 'imageArray'">
              <div class="form_image_array_wrapper">
                <div class="form_image_wrapper mb-2 mr-2" v-for="(iii,iik) in edit_data.form[k][ik]">
                  <div class="form_image_delete_wrapper">
                    <el-button @click="imageDeleteClick(k, ik, iik)" type="danger" size="small">
                      <Icon type="delete" :size="12"></Icon>
                    </el-button>
                  </div>
                  <el-upload :auto-upload="false" :show-file-list="false" @change="(e)=>{fileChange(e,k,ik,iik)}">
                    <el-image class="form_image_show_wrapper" :src="$image(iii)" fit="contain"></el-image>
                  </el-upload>
                </div>
                <div class="form_image_wrapper mb-2 mr-2">
                  <el-upload :auto-upload="false" :show-file-list="false" @change="(e)=>{fileChange(e,k,ik,-1)}">
                    <div class="form_image_empty_wrapper">上传图片</div>
                  </el-upload>
                </div>
              </div>
            </template>
            <template v-else-if="formType(ii.type) === 'json'">
              <JsonEditorVue language="zh-CN" :modeList="[]" class="form_json_wrapper" v-model="edit_data.form[k][ik]"/>
            </template>
            <template v-else-if="formType(ii.type) === 'richText'">
              <Tinymce :ref="(e)=>{richTextRef(e,ik)}" :content="edit_data.form[k][ik]" :width="900"></Tinymce>
            </template>
            <template v-else-if="formType(ii.type) === 'switch'">
              <el-switch v-model="edit_data.form[k][ik]" inline-prompt
                         style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949"
                         active-text="开启" inactive-text="关闭" active-value="1" inactive-value="0"/>
            </template>
            <template v-else-if="formType(ii.type) === 'color'">
              <el-color-picker :predefine="predefine_config" v-model="edit_data.form[k][ik]" show-alpha/>
            </template>
            <template v-else>
              <el-input v-model="edit_data.form[k][ik]" :placeholder="ii.placeholder"></el-input>
            </template>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="edit_show = false">取消</el-button>
        <el-button type="primary" @click="editDone()">确认</el-button>
      </div>
    </template>
  </el-dialog>

  <div>
    <slot name="header"></slot>
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
            <slot name="buttonLeft"></slot>
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
            <el-checkbox @change="editActiveChange(scope.$index)"
                         v-model="table_list[scope.$index].EDIT_ACTIVE"></el-checkbox>
          </template>
        </el-table-column>
        <el-table-column v-for="(i,k) in database_info.list.table" :key="k" :label="i.label" :width="i.width">
          <template #default="scope">
            <div v-if="!!scope.row[i.value]" class="table_column_wrapper" :style="{
              width: !!i.width ? `calc(${i.width}px - 30px)` : 'calc(100% - 20px)'
            }">
              <div class="table_column_wrapper w-full" v-if="formType(i.type, scope.row, true) === 'stringArray'">
                <div w-full
                     v-if="'tooltip' in i && !!i.tooltip && JSON.parse(scope.row[i.value]).join(' ').length > i.tooltip">
                  <el-tooltip effect="dark" :content="JSON.parse(scope.row[i.value]).join(' ')" placement="top">
                    <div class="table_column_string_wrapper">{{ JSON.parse(scope.row[i.value]).join(' ') }}</div>
                  </el-tooltip>
                </div>
                <div v-else class="table_column_string_wrapper">{{ JSON.parse(scope.row[i.value]).join(' ') }}</div>
              </div>
              <div class="table_column_wrapper w-full" v-else-if="formType(i.type, scope.row, true) === 'imageArray'">
                <div class="mr-2" v-for="(ii,ik) in JSON.parse(scope.row[i.value])">
                  <el-image :key="ik" v-if="ik < 3" preview-teleported :initial-index="ik"
                            :preview-src-list="JSON.parse(scope.row[i.value]).map((item)=>$image(item))"
                            class="table_column_image_wrapper" :src="$image(ii)" fit="contain"></el-image>
                </div>
                <span v-if="JSON.parse(scope.row[i.value]).length > 3">...</span>
              </div>
              <div class="table_column_wrapper w-full" v-else-if="formType(i.type, scope.row, true) === 'image'">
                <el-image preview-teleported :previewSrcList="[$image(scope.row[i.value])]"
                          class="table_column_image_wrapper" :src="$image(scope.row[i.value])" fit="contain"></el-image>
              </div>
              <div class="table_column_wrapper w-full" v-else-if="formType(i.type, scope.row, true) === 'json'">
                <el-button @click="jsonShow(scope.row[i.value])" size="small" type="primary">查看</el-button>
              </div>
              <div class="table_column_wrapper w-full" v-else-if="formType(i.type, scope.row, true) === 'richText'">
                <el-button @click="richTextShow(scope.row[i.value])" size="small" type="primary">查看</el-button>
              </div>
              <div class="table_column_wrapper w-full" v-else-if="formType(i.type, scope.row, true) === 'switch'">
                <div class="table_column_switch_wrapper" :style="{
                  background: scope.row[i.value] === '1' ? '#13ce66' : '#ff4949'
                }">{{ scope.row[i.value] === '1' ? '开启' : '关闭' }}
                </div>
              </div>
              <div class="table_column_wrapper w-full" v-else-if="formType(i.type, scope.row, true) === 'color'">
                <div class="table_column_color_wrapper" :style="{
                  background: scope.row[i.value]
                }"></div>
                <div w-full v-if="'tooltip' in i && !!i.tooltip && scope.row[i.value].length > i.tooltip">
                  <el-tooltip effect="dark" :content="scope.row[i.value]" placement="top">
                    <div class="table_column_string_wrapper ml-2">{{ scope.row[i.value] }}</div>
                  </el-tooltip>
                </div>
                <div v-else class="table_column_string_wrapper ml-2">{{ scope.row[i.value] }}</div>
              </div>
              <div class="w-full" v-else>
                <div w-full v-if="'tooltip' in i && !!i.tooltip && valueShow(scope.row[i.value], i).length > i.tooltip">
                  <el-tooltip effect="dark" :content="valueShow(scope.row[i.value], i)" placement="top">
                    <div class="table_column_string_wrapper">{{ valueShow(scope.row[i.value], i) }}</div>
                  </el-tooltip>
                </div>
                <div v-else class="table_column_string_wrapper">{{ valueShow(scope.row[i.value], i) }}</div>
              </div>
            </div>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination v-if="last_page > 0" :current-page="page_options.page" mt-2 background layout="prev, pager, next"
                     :page-count="last_page" @update:current-page="searchClick"/>
    </div>
  </div>
</template>
<style scoped>
.table_column_wrapper {
  display: flex;
  align-items: center;

  .table_column_string_wrapper {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .table_column_image_wrapper {
    width: 40px;
    height: 40px;
  }

  .table_column_color_wrapper {
    height: 20px;
    font-size: 14px;
    width: 20px;
    text-align: center;
    display: inline-block;
  }

  .table_column_switch_wrapper {
    height: 20px;
    line-height: 20px;
    font-size: 14px;
    width: 60px;
    text-align: center;
    color: #ffffff;
  }
}

.table_button_wrapper {
  display: flex;
  align-items: center;
  justify-content: space-between;

  .table_button_group_wrapper {
    display: flex;
    align-items: center;
  }
}

.form_col_wrapper {
  display: flex;
  justify-content: space-between;
  width: 100%;

  .form_image_array_wrapper {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
  }

  .form_string_array_wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;

    .form_string_array_input_wrapper {
      width: calc(100% - 60px);
    }
  }

  .form_json_wrapper {
    height: 300px;
  }

  .form_image_wrapper {
    width: 80px;
    height: 80px;
    aspect-ratio: 1/1;
    background-image: linear-gradient(45deg, #eee 25%, transparent 25%, transparent 75%, #eee 75%), linear-gradient(45deg, #eee 25%, transparent 25%, transparent 75%, #eee 75%);
    background-size: 16px 16px;
    background-position: 0 0, 8px 8px;
    position: relative;

    .form_image_delete_wrapper {
      display: none;
      position: absolute;
      z-index: 999;
      top: 0;
      right: 5px;
    }

    .form_image_show_wrapper {
      width: 80px;
      height: 80px;
    }

    .form_image_empty_wrapper {
      width: 80px;
      height: 80px;
      line-height: 80px;
      font-size: 14px;
      text-align: center;
    }
  }

  .form_image_wrapper:hover {
    .form_image_delete_wrapper {
      display: block;
    }
  }
}
</style>

