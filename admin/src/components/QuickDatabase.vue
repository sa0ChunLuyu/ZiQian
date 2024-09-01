<script setup>
/**
 * name：QuickDatabase
 * user：sa0ChunLuyu
 * date：2024年8月26日 10:43:09
 */
import {$api, $base64, $image, $response, $url} from '~/api'
import {onBeforeRouteUpdate} from "vue-router";
import $router from "~/router";
import JsonEditorVue from 'json-editor-vue3'
import VueJsonPretty from 'vue-json-pretty'
import IconsJson from '@icon-park/vue-next/icons.json';

const $props = defineProps({
  database: {
    type: String,
    default: ''
  }
})
const iconCollapseChange = (e) => {
  icon_collapse.value = e
}
const icons_search = ref('')
const icon_collapse = ref('')
const icons_show = ref(true)
const icons_list = computed(() => {
  icons_show.value = false
  let list = []
  let search = icons_search.value
  if (!!search) {
    for (let i = 0; i < IconsJson.length; i++) {
      let push = false
      if (IconsJson[i].title.toLowerCase().indexOf(search.toLowerCase()) !== -1) push = true
      if (IconsJson[i].name.toLowerCase().indexOf(search.toLowerCase()) !== -1) push = true
      if (IconsJson[i].category.toLowerCase().indexOf(search.toLowerCase()) !== -1) push = true
      if (IconsJson[i].categoryCN.toLowerCase().indexOf(search.toLowerCase()) !== -1) push = true
      for (let ii = 0; ii < IconsJson[i].tag.length; ii++) {
        if (IconsJson[i].tag[ii].toLowerCase().indexOf(search.toLowerCase()) !== -1) push = true
      }
      if (push) list.push(IconsJson[i])
    }
  } else {
    list = IconsJson
  }
  let list_turn = {}
  for (let i = 0; i < list.length; i++) {
    if (!(list[i]['category'] in list_turn)) {
      list_turn[list[i]['category']] = {
        name: list[i]['category'],
        nameCN: list[i]['categoryCN'],
        children: []
      }
    }
    list_turn[list[i]['category']]['children'].push({
      title: list[i]['title'],
      name: list[i]['name'],
    })
  }
  let ret = []
  for (let i in list_turn) {
    ret.push(list_turn[i])
  }
  nextTick(() => {
    icons_show.value = true
  })
  return ret
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
    table_list.value = []
    nextTick(() => {
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
        let default_value = String(JSON.parse(JSON.stringify(database_info.value.form[i][ii].value)))
        if (default_value.includes('query:')) {
          let default_value_array = default_value.split(':')
          default_value = search_form.value[default_value_array[1]].value
          if (default_value_array[2] === 'number') {
            default_value = Number(default_value)
          }
          form[ii] = default_value
        } else {
          form[ii] = JSON.parse(JSON.stringify(database_info.value.form[i][ii].value))
        }
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

const icon_show = ref(false)

const iconChooseClick = (icon) => {
  edit_data.value.form[icon_index.value][icon_active.value] = ''
  nextTick(() => {
    edit_data.value.form[icon_index.value][icon_active.value] = icon
  })
  icon_show.value = false
}
const icon_active = ref('')
const icon_index = ref(0)
const iconClick = (index, active) => {
  icon_index.value = index
  icon_active.value = active
  icon_show.value = true
  icons_show.value = true
}

const value_view_content = ref('')
const value_view_type = ref('')
const value_view_show = ref(false)
const valueViewShow = (value, type) => {
  value_view_type.value = type
  switch (type) {
    case 'richText':
      value_view_content.value = value
      break
    case 'json':
      value_view_content.value = JSON.parse(value)
      break
  }
  value_view_show.value = true
}

const downImportTemplate = async () => {
  const response = await $api('AdminQuickDatabaseImportTemplate', {
    database: $props.database
  })
  $response(response, () => {
    window.open($image(response.data.url))
  })
}

const downExport = async (type = 'page') => {
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
  if (type === 'all') {
    q.page = 'all'
  }
  const response = await $api('AdminQuickDatabaseExport', {
    database: $props.database,
    ...q
  })
  $response(response, () => {
    window.open($image(response.data.url))
  })
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
  <el-drawer v-model="icon_show" title="图标选择">
    <div>
      <el-form>
        <el-form-item label="搜索">
          <el-input class="input_line_input_wrapper" v-model="icons_search"
                    placeholder="请输入"></el-input>
        </el-form-item>
      </el-form>
      <div mt-2>
        <el-collapse v-for="(i,k) in icons_list" accordion @change="iconCollapseChange">
          <el-collapse-item :title="i.nameCN" :name="i.name">
            <el-row v-if="i.name === icon_collapse">
              <el-col :span="4" v-for="(ii,kk) in i.children" :key="kk">
                <div @click="iconChooseClick(ii.name)" cursor-pointer text-center m-2>
                  <div>
                    <Icon v-if="icons_show" :type="ii.name"></Icon>
                  </div>
                  <div>{{ ii.title }}</div>
                </div>
              </el-col>
            </el-row>
          </el-collapse-item>
        </el-collapse>
      </div>
    </div>
  </el-drawer>
  <el-dialog v-model="value_view_show" title="内容查看" :width="1000">
    <el-scrollbar height="300px">
      <div v-if="value_view_type === 'richText'" v-html="value_view_content"></div>
      <VueJsonPretty v-else-if="value_view_type === 'json'" :data="value_view_content"></VueJsonPretty>
      <div v-else>{{ value_view_content }}</div>
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
                         :empty-values="[null, undefined]" :placeholder="ii.placeholder" :disabled="ii.disabled">
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
                <el-upload :disabled="ii.disabled" :auto-upload="false" :show-file-list="false"
                           @change="(e)=>{fileChange(e,k,ik,-2)}">
                  <el-image class="form_image_show_wrapper" v-if="!!edit_data.form[k][ik]"
                            :src="$image(edit_data.form[k][ik])" fit="contain"></el-image>
                  <div v-else class="form_image_empty_wrapper">上传图片</div>
                </el-upload>
              </div>
            </template>
            <template v-else-if="formType(ii.type) === 'textarea'">
              <el-input :disabled="ii.disabled" type="textarea" v-model="edit_data.form[k][ik]"
                        :placeholder="ii.placeholder"></el-input>
            </template>
            <template v-else-if="formType(ii.type) === 'stringArray'">
              <div w-full>
                <div v-for="(iii,iik) in edit_data.form[k][ik]" class="form_string_array_wrapper mb-2">
                  <el-input class="form_string_array_input_wrapper" v-model="edit_data.form[k][ik][iik]"
                            :placeholder="ii.placeholder" :disabled="ii.disabled"></el-input>
                  <el-button @click="stringArrayDeleteClick(k, ik, iik)" text>
                    <Icon type="delete"></Icon>
                  </el-button>
                </div>
                <div class="form_string_array_wrapper">
                  <el-input class="form_string_array_input_wrapper" v-model="string_array_input"
                            :placeholder="ii.placeholder" :disabled="ii.disabled"></el-input>
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
                  <el-upload :disabled="ii.disabled" :auto-upload="false" :show-file-list="false"
                             @change="(e)=>{fileChange(e,k,ik,iik)}">
                    <el-image class="form_image_show_wrapper" :src="$image(iii)" fit="contain"></el-image>
                  </el-upload>
                </div>
                <div class="form_image_wrapper mb-2 mr-2">
                  <el-upload :disabled="ii.disabled" :auto-upload="false" :show-file-list="false"
                             @change="(e)=>{fileChange(e,k,ik,-1)}">
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
              <el-switch :disabled="ii.disabled" v-model="edit_data.form[k][ik]" inline-prompt
                         style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949"
                         active-text="开启" inactive-text="关闭" active-value="1" inactive-value="0"/>
            </template>
            <template v-else-if="formType(ii.type) === 'color'">
              <el-color-picker :disabled="ii.disabled" :predefine="predefine_config" v-model="edit_data.form[k][ik]"
                               show-alpha/>
            </template>
            <template v-else-if="formType(ii.type) === 'number'">
              <el-input-number :disabled="ii.disabled" v-model="edit_data.form[k][ik]"
                               :placeholder="ii.placeholder" :min="ii.min" :max="ii.max"
                               :step="ii.step"></el-input-number>
            </template>
            <template v-else-if="formType(ii.type) === 'icon'">
              <div cursor-pointer @click="iconClick(k, ik)" class="form_icon_wrapper" text-center>
                <el-icon>
                  <Icon v-if="!!edit_data.form[k][ik]" :type="edit_data.form[k][ik]"></Icon>
                </el-icon>
              </div>
            </template>
            <template v-else>
              <el-input :disabled="ii.disabled" v-model="edit_data.form[k][ik]"
                        :placeholder="ii.placeholder"></el-input>
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
        <template v-if="i.type === 'select'">
          <div class="form_input_wrapper">
            <el-select :empty-values="[null, undefined]" v-model="search_form[k].value" :placeholder="i.placeholder">
              <el-option v-for="(ii,ik) in i.select" :key="ik" :label="ii.label" :value="ii.value"></el-option>
            </el-select>
          </div>
        </template>
        <template v-else-if="i.type === 'datetimerange'">
          <el-date-picker v-model="search_form[k].value"
                          start-placeholder="开始时间"
                          end-placeholder="结束时间"
                          type="datetimerange" format="YYYY-MM-DD HH:mm:ss"
                          value-format="YYYY-MM-DD HH:mm:ss" class="form_input_wrapper"/>
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
          <div>
            <slot name="buttonLeft"></slot>
          </div>
        </div>
        <div class="table_button_group_wrapper">
          <el-dropdown v-if="database_info.list.button.includes('export')">
            <el-button type="warning">导出</el-button>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item @click="downExport()">
                  导出当前
                </el-dropdown-item>
                <el-dropdown-item @click="downExport('all')">
                  全部导出
                </el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </div>
      <el-table mt-2 border :data="table_list" style="width: 100%">
        <el-table-column v-if="!('checkbox' in database_info.list) || !!database_info.list.checkbox" label=""
                         width="40">
          <template #default="scope">
            <el-checkbox @change="editActiveChange(scope.$index)"
                         v-model="table_list[scope.$index].EDIT_ACTIVE"></el-checkbox>
          </template>
        </el-table-column>
        <el-table-column :class-name="i.type === 'desc' ? 'table_desc_wrapper' : ''"
                         v-for="(i,k) in database_info.list.table" :key="k"
                         :label="i.label" :width="i.width">
          <template #default="scope">
            <div v-if="['file', 'desc'].includes(i.type) || typeof scope.row[i.value] !== 'undefined'" :style="{
              width: !!i.width ? `calc(${i.width}px - 30px)` : 'calc(100% - 20px)'
            }">
              <QuickDatabaseShow :type="formType(i.type, scope.row, true)" :column="i" :row="scope.row"
                                 :form="database_info.form"></QuickDatabaseShow>
            </div>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination v-if="last_page > 0" :current-page="page_options.page" mt-2 background layout="prev, pager, next"
                     :page-count="last_page" @update:current-page="searchClick"/>
    </div>
  </div>
</template>
<style>
.el-table__body {
  .table_desc_wrapper {
    padding: 0 !important;

    .cell {
      padding: 0 !important;
    }
  }
}
</style>
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

.form_col_wrapper {
  display: flex;
  justify-content: space-between;
  width: 100%;

  .form_icon_wrapper {
    width: 100%;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--el-input-bg-color, var(--el-fill-color-blank));
    background-image: none;
    border-radius: var(--el-input-border-radius, var(--el-border-radius-base));
    transition: var(--el-transition-box-shadow);
    transform: translate3d(0, 0, 0);
    box-shadow: 0 0 0 1px var(--el-input-border-color, var(--el-border-color));
  }

  .form_icon_wrapper:hover {
    box-shadow: 0 0 0 1px var(--el-border-color-hover);
  }

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

