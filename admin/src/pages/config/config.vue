<script setup>
/**
 * name：
 * user：sa0ChunLuyu
 * date：2023年8月11日 19:35:59
 */
import {
  $api,
  $response,
  $image,
  $base64
} from '~/api'
import VueJsonPretty from 'vue-json-pretty'
import JsonEditorVue from 'json-editor-vue3'

const table_list = ref([])
const ConfigList = async () => {
  table_list.value = []
  const response = await $api('AdminConfigList')
  $response(response, () => {
    table_list.value = response.data.list
  })
}

const fileChange = async (e, key) => {
  if (e.size > 1024 * 1024 * 2) return window.$message().error('图片大小不能超过2M')
  await UploadImage(await $base64(e.raw), key)
}
const UploadImage = async (base64, key) => {
  const response = await $api('AdminUploadImage', {
    base64
  })
  $response(response, () => {
    if (key === -1) {
      edit_data.value.value = response.data.url
    } else if (key === -2) {
      edit_data.value.value.push(response.data.url)
    } else {
      edit_data.value.value[key] = response.data.url
    }
  })
}
const editCloseClick = () => {
  edit_show.value = false
}
const editDoneClick = async () => {
  let response
  let data = JSON.parse(JSON.stringify(edit_data.value))
  data.value = [3, 4, 5].indexOf(edit_data.value.type) !== -1 ? JSON.stringify(edit_data.value.value) : edit_data.value.value
  if (edit_data.value.type === 6) data.value = editor_ref.value.getContent()
  if (data.id === 0) {
    response = await $api('AdminConfigCreate', data)
  } else {
    data.password = 'placeholder'
    response = await $api('AdminConfigUpdate', data)
  }
  $response(response, () => {
    edit_show.value = false
    table_ref.value.setCurrentRow(null)
    ConfigList()
    window.$message().success(data.id === 0 ? '创建成功' : '修改成功')
    edit_data.value = JSON.parse(JSON.stringify(default_data))
  })
}

const table_ref = ref(null)
const tableRef = (e) => {
  table_ref.value = e
}
const tableRowClick = (e) => {
  if (e.id === edit_data.value.id) {
    edit_data.value = JSON.parse(JSON.stringify(default_data))
    table_ref.value.setCurrentRow(null)
  } else {
    edit_data.value = JSON.parse(JSON.stringify(e))
    table_ref.value.setCurrentRow(e)
  }
}

const edit_show = ref(false)
const default_data = {
  id: 0,
  name: '',
  value: '',
  type: 1,
  client: 0,
  login: 2,
  remark: '',
}
const edit_data = ref(JSON.parse(JSON.stringify(default_data)))
const type_arr = ['文字', '图片', '文字数组', '图片数组', 'JSON', '富文本', '开关', '颜色', '单选']
const editClick = async (type) => {
  if (type === 0) {
    table_ref.value.setCurrentRow(null)
    edit_data.value = JSON.parse(JSON.stringify(default_data))
  }
  edit_show.value = true
}
const add_input = ref('')
const addInputClick = () => {
  edit_data.value.value.push(add_input.value)
  add_input.value = ''
}
const typeChange = (e) => {
  if ([3, 4].indexOf(e) !== -1) {
    edit_data.value.value = []
  } else if ([5].indexOf(e) !== -1) {
    edit_data.value.value = {}
  } else if (e === 7) {
    edit_data.value.value = '#000000'
  } else if (e === 8) {
    edit_data.value.value = '0'
  } else {
    edit_data.value.value = ''
  }
}
const deleteItemClick = (key) => {
  edit_data.value.value.splice(key, 1)
}

const ConfigDelete = async () => {
  const response = await $api('AdminConfigDelete', {
    id: edit_data.value.id
  })
  $response(response, () => {
    window.$message().success('删除成功')
    edit_data.value = JSON.parse(JSON.stringify(default_data))
    table_ref.value.setCurrentRow(null)
    const index = table_list.value.findIndex(item => item.id === response.data.id)
    table_list.value.splice(index, 1)
  })
}
const deleteClick = () => {
  window.$box.confirm(
      '是否确认删除该参数？',
      '注意！删除后会影响系统运行！',
      {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning',
      }
  ).then(() => {
    ConfigDelete()
  }).catch(() => {
  })
}

const editor_ref = ref(null)
const editorRef = (e) => {
  editor_ref.value = e
}
const predefine_arr = [
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
const changeIndexClick = (key, type) => {
  const value = JSON.parse(JSON.stringify(edit_data.value.value[key]))
  edit_data.value.value.splice(key, 1)
  edit_data.value.value.splice(key + type, 0, value)
}
onMounted(() => {
  ConfigList()
})
</script>
<template>
  <div>
    <el-dialog v-model="edit_show" :title="!!edit_data.id ? '编辑' : '新建'" width="1040px"
               :close-on-click-modal="false"
               :close-on-press-escape="false"
               :show-close="false">
      <div>
        <div class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">名称</el-tag>
          </div>
          <div ml-2>
            <el-input class="input_line_input_wrapper" v-model="edit_data.name"
                      placeholder="请输入名称"></el-input>
          </div>
        </div>
        <div mt-2 class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">类型</el-tag>
          </div>
          <div ml-2>
            <el-select v-model="edit_data.type"
                       class="input_line_input_wrapper"
                       placeholder="请选择类型" @change="typeChange">
              <el-option :disabled="i.id === -1" v-for="(i,k) in type_arr.map((item,key)=>{
                return {
                  id: key+1,
                  name: item,
                }
              })" :key="k" :label="i.name" :value="i.id"/>
            </el-select>
          </div>
        </div>
        <div mt-2 class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">内容</el-tag>
          </div>
          <div v-if="edit_data.type === 1" ml-2>
            <el-input class="input_line_input_wrapper" v-model="edit_data.value"
                      placeholder="请输入内容"></el-input>
          </div>
          <div v-if="edit_data.type === 2" ml-2>
            <div>
              <div class="table_image_wrapper">
                <div class="image_button_wrapper">
                  <el-button v-if="!!edit_data.value" @click="edit_data.value = ''" type="danger">删除</el-button>
                </div>
                <el-image :preview-src-list="[$image(edit_data.value)]" class="image_box_wrapper" fit="contain"
                          :src="$image(edit_data.value)">
                  <template #error>
                    <div class="image_error_wrapper">暂无图片</div>
                  </template>
                </el-image>
              </div>
              <div mt-2 class="input_line_wrapper">
                <el-upload :auto-upload="false" :show-file-list="false" @change="(e)=>fileChange(e,-1)">
                  <el-button type="primary">上传</el-button>
                </el-upload>
              </div>
            </div>
          </div>
          <div v-if="edit_data.type === 3" ml-2>
            <div class="input_line_wrapper">
              <el-input class="input_line_input_wrapper" v-model="add_input"
                        placeholder="请输入内容"></el-input>
              <el-button @click="addInputClick()" ml-1 type="primary">添加</el-button>
            </div>
          </div>
          <div v-if="edit_data.type === 4" ml-2>
            <div>
              <div class="input_line_wrapper">
                <el-upload :auto-upload="false" :show-file-list="false" @change="(e)=>fileChange(e,-2)">
                  <el-button type="primary">上传</el-button>
                </el-upload>
              </div>
            </div>
          </div>
          <div v-if="edit_data.type === 7" ml-2>
            <div>
              <el-switch v-model="edit_data.value" inline-prompt
                         style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949"
                         active-text="开启" inactive-text="关闭" active-value="1" inactive-value="0"/>
            </div>
          </div>
          <div v-if="edit_data.type === 8" ml-2>
            <div>
              <el-color-picker :predefine="predefine_arr" v-model="edit_data.value" show-alpha/>
            </div>
          </div>
        </div>
        <div v-if="[3,4,5,6].indexOf(edit_data.type) !== -1">
          <div v-if="edit_data.type === 3">
            <div my-1 class="input_line_wrapper" v-for="(i,k) in edit_data.value" :key="k">
              <div class="input_line_tag_wrapper"></div>
              <div ml-2>
                <el-input class="input_line_input_wrapper" v-model="edit_data.value[k]"
                          placeholder="请输入内容"></el-input>
              </div>
              <div>
                <el-button-group>
                  <el-button @click="changeIndexClick(k,-1)" :disabled="k === 0" ml-1 type="primary">上移</el-button>
                  <el-button @click="changeIndexClick(k,1)" :disabled="k === edit_data.value.length - 1" ml-1
                             type="primary">下移
                  </el-button>
                  <el-button @click="deleteItemClick(k)" :disabled="edit_data.value.length <= 1" ml-1 type="danger">删除
                  </el-button>
                </el-button-group>
              </div>
            </div>
          </div>
          <div v-if="edit_data.type === 4">
            <el-row>
              <el-col :span="8" v-for="(i,k) in edit_data.value" :key="k">
                <div m-2>
                  <div>
                    <div class="table_image_wrapper">
                      <div class="image_button_wrapper">
                        <el-button-group size="small">
                          <el-button @click="changeIndexClick(k,-1)" :disabled="k === 0" ml-1 type="primary">上移
                          </el-button>
                          <el-button @click="changeIndexClick(k,1)" :disabled="k === edit_data.value.length - 1" ml-1
                                     type="primary">下移
                          </el-button>
                          <el-button @click="deleteItemClick(k)" :disabled="edit_data.value.length <= 1" ml-1
                                     type="danger">删除
                          </el-button>
                        </el-button-group>
                      </div>
                      <el-image :preview-src-list="edit_data.value.map((item)=>$image(item))" :initial-index="k"
                                class="image_box_wrapper"
                                fit="contain" :src="$image(i)">
                        <template #error>
                          <div class="image_error_wrapper">暂无图片</div>
                        </template>
                      </el-image>
                    </div>
                    <div mt-2 class="input_line_wrapper" justify-center>
                      <el-upload :auto-upload="false" :show-file-list="false" @change="(e)=>fileChange(e,k)">
                        <el-button type="primary">上传</el-button>
                      </el-upload>
                    </div>
                  </div>
                </div>
              </el-col>
            </el-row>
          </div>
          <div v-if="edit_data.type === 5">
            <JsonEditorVue language="zh-CN" :modeList="[]" style="height: 400px;" mt-2 v-model="edit_data.value"/>
          </div>
          <div v-if="edit_data.type === 6">
            <Tinymce v-if="!!edit_show" mt-2 :ref="editorRef" :content="edit_data.value"></Tinymce>
          </div>
        </div>
        <div mt-2 class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">归属类型</el-tag>
          </div>
          <div ml-2>
            <el-select v-model="edit_data.client"
                       class="input_line_input_wrapper"
                       placeholder="请选择归属类型">
              <el-option :disabled="i.id === -1" v-for="(i,k) in [
                  {id:0,name:'公共'},
                  {id:1,name:'后台'},
                ]" :key="k" :label="i.name" :value="i.id"/>
            </el-select>
          </div>
        </div>
        <div mt-2 class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">登录类型</el-tag>
          </div>
          <div ml-2>
            <el-select v-model="edit_data.login"
                       class="input_line_input_wrapper"
                       placeholder="请选择登录类型">
              <el-option label="登录获取" :value="1"/>
              <el-option label="随时获取" :value="2"/>
            </el-select>
          </div>
        </div>
        <div mt-2 class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">备注</el-tag>
          </div>
          <div ml-2>
            <el-input class="input_line_input_wrapper" v-model="edit_data.remark"
                      placeholder="请输入备注"></el-input>
          </div>
        </div>
      </div>
      <template #footer>
        <div class="dialog-footer">
          <el-button @click="editCloseClick()">关闭</el-button>
          <el-button @click="editDoneClick()" type="primary">保存</el-button>
        </div>
      </template>
    </el-dialog>

    <el-card>
      <template #header>后台配置</template>
      <div>
        <div>
          <el-button @click="editClick(0)" type="primary">新建</el-button>
          <el-button :disabled="edit_data.id === 0" @click="editClick(1)" type="success">编辑</el-button>
          <el-button :disabled="edit_data.id === 0" @click="deleteClick()" type="danger">删除</el-button>
        </div>
        <el-table border row-class-name="cursor-pointer" mt-2 :data="table_list" highlight-current-row
                  style="width: 100%"
                  @row-click="tableRowClick" :ref="tableRef">
          <el-table-column type="expand">
            <template #default="props">
              <div class="config_item_wrapper" p-2>
                <div v-if="props.row.type === 1">
                  {{ props.row.value }}
                </div>
                <div v-else-if="props.row.type === 2">
                  <div class="table_image_wrapper">
                    <el-image :preview-src-list="[$image(props.row.value)]" class="image_box_wrapper" fit="contain"
                              :src="$image(props.row.value)" preview-teleported>
                      <template #error>
                        <div class="image_error_wrapper">暂无图片</div>
                      </template>
                    </el-image>
                  </div>
                </div>
                <div v-else-if="props.row.type === 3">
                  <div my-1 v-for="(i,k) in props.row.value" :key="k">{{ i }}</div>
                </div>
                <div v-else-if="props.row.type === 4">
                  <el-row>
                    <el-col :span="4" v-for="(i,k) in props.row.value" :key="k">
                      <div m-2>
                        <el-image :preview-src-list="[$image(i)]" class="image_box_wrapper" fit="contain"
                                  :src="$image(i)" preview-teleported>
                          <template #error>
                            <div class="image_error_wrapper">暂无图片</div>
                          </template>
                        </el-image>
                      </div>
                    </el-col>
                  </el-row>
                </div>
                <div v-else-if="props.row.type === 5">
                  <el-scrollbar height="300px">
                    <VueJsonPretty :data="props.row.value"></VueJsonPretty>
                  </el-scrollbar>
                </div>
                <div v-else-if="props.row.type === 6" text-left>
                  <el-scrollbar height="300px">
                    <div v-html="props.row.value"></div>
                  </el-scrollbar>
                </div>
                <div v-else-if="props.row.type === 7">
                  <div class="table_switch_wrapper">
                    <div class="table_switch_cover_wrapper"></div>
                    <el-switch v-model="props.row.value" inline-prompt
                               style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949"
                               active-text="开启" inactive-text="关闭" active-value="1" inactive-value="0"/>
                  </div>
                </div>
                <div v-else-if="props.row.type === 8">
                  <div class="table_color_wrapper" :style="{
                    background: props.row.value
                  }"></div>
                </div>
              </div>
            </template>
          </el-table-column>
          <el-table-column label="名称" prop="name"/>
          <el-table-column label="类型" width="120">
            <template #default="scope">
              <el-tag disable-transitions type="success">{{ type_arr[scope.row.type - 1] }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="归属类型" width="120">
            <template #default="scope">
              <el-tag disable-transitions v-if="scope.row.client === 0">公共</el-tag>
              <el-tag disable-transitions v-else-if="scope.row.client === 1" type="warning">后台</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="登录类型" width="120">
            <template #default="scope">
              <el-tag disable-transitions v-if="scope.row.login === 1" type="warning">登录获取</el-tag>
              <el-tag disable-transitions v-else-if="scope.row.login === 2" type="success">随时获取</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="备注" prop="remark"/>
        </el-table>
      </div>
    </el-card>
  </div>
</template>
<style>
a.jsoneditor-poweredBy {
  font-size: 8pt;
  position: absolute;
  right: 0;
  top: 0;
  display: none;
}
</style>
<style scoped>
.table_color_wrapper {
  width: 100px;
  height: 60px;
  margin: 0 auto;
  border-radius: 6px;
}

.table_switch_wrapper {
  position: relative;
}

.table_switch_cover_wrapper {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background: #00000000;
  z-index: 99;
}

.config_item_wrapper {
  text-align: center;
}

.col_wrapper {
  width: 100%;
  height: 50px;
  position: relative;
}

.image_button_wrapper {
  position: absolute;
  display: flex;
  justify-content: center;
  top: 10px;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  z-index: 99;
}

.image_wrapper {
  width: 100%;
  aspect-ratio: 1/1;
  background: #cccccc;
  border-radius: 6px;
  overflow: hidden;
  position: relative;
}

.table_image_wrapper {
  width: 200px;
  aspect-ratio: 1/1;
  background: #cccccc;
  border-radius: 6px;
  overflow: hidden;
  position: relative;
  margin: 0 auto;
}

.image_box_wrapper {
  width: 100%;
  aspect-ratio: 1/1;
  background-image: linear-gradient(45deg, #eee 25%, transparent 25%, transparent 75%, #eee 75%), linear-gradient(45deg, #eee 25%, transparent 25%, transparent 75%, #eee 75%);
  background-size: 16px 16px;
  background-position: 0 0, 8px 8px;
}

.image_error_wrapper {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  color: #333333;
}
</style>
<route>
{"meta":{"title":"后台配置"}}
</route>
