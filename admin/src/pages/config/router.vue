<script setup>
/**
 * name：
 * user：sa0ChunLuyu
 * date：2023年8月11日 19:35:59
 */
import {
  $api,
  $response
} from '~/api'
import icons_json from '@icon-park/vue-next/icons.json';

const icons_search = ref('')
const icons_show = ref(true)
const icons_list = computed(() => {
  icons_show.value = false
  let list = []
  let search = icons_search.value
  if (!!search) {
    for (let i = 0; i < icons_json.length; i++) {
      let push = false
      if (icons_json[i].title.toLowerCase().indexOf(search.toLowerCase()) !== -1) push = true
      if (icons_json[i].name.toLowerCase().indexOf(search.toLowerCase()) !== -1) push = true
      if (icons_json[i].category.toLowerCase().indexOf(search.toLowerCase()) !== -1) push = true
      if (icons_json[i].categoryCN.toLowerCase().indexOf(search.toLowerCase()) !== -1) push = true
      for (let ii = 0; ii < icons_json[i].tag.length; ii++) {
        if (icons_json[i].tag[ii].toLowerCase().indexOf(search.toLowerCase()) !== -1) push = true
      }
      if (push) list.push(icons_json[i])
    }
  } else {
    list = icons_json
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
  setTimeout(() => {
    icons_show.value = true
  })
  return ret
})

const table_list = ref([])
const AdminAuthList = async () => {
  const response = await $api('AdminAdminAuthList')
  $response(response, () => {
    table_list.value = response.data.list.map((item) => {
      if ('list' in item && item.list.length > 0) {
        return {
          ...item.info,
          children: item.list
        }
      } else {
        return item.info
      }
    })
  })
}
onMounted(() => {
  AdminAuthSelect()
  AdminAuthList()
})
const admin_auth_select = ref([])
const AdminAuthSelect = async () => {
  const response = await $api('AdminAdminAuthSelect')
  $response(response, () => {
    admin_auth_select.value = response.data.list
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
  title: '',
  icon: '',
  pid: 0,
  type: 1,
  check: 2,
  show: 1,
  status: 1,
  message: '',
  order: 0,
}
const edit_data = ref(JSON.parse(JSON.stringify(default_data)))
const editClick = async (type) => {
  if (type === 0) {
    table_ref.value.setCurrentRow(null)
    edit_data.value = JSON.parse(JSON.stringify({
      ...default_data,
      pid: edit_data.value.type === 1 ? edit_data.value.id : 0,
      type: (edit_data.value.type === 1 && edit_data.value.id !== 0) ? 2 : 1,
    }))
  } else {
    const icon = JSON.parse(JSON.stringify(edit_data.value.icon))
    edit_data.value.icon = ''
    nextTick(() => {
      edit_data.value.icon = icon
    })
  }
  edit_show.value = true
}
const AdminDelete = async () => {
  const response = await $api('AdminAdminAuthDelete', {
    id: edit_data.value.id
  })
  $response(response, () => {
    window.$message().success('删除成功')
    table_ref.value.setCurrentRow(null)
    if (edit_data.value.pid === 0) {
      const index = table_list.value.findIndex(item => item.id === response.data.id)
      table_list.value.splice(index, 1)
      const select_index = admin_auth_select.value.findIndex(item => item.id === response.data.id)
      admin_auth_select.value.splice(select_index, 1)
    } else {
      const index = table_list.value.findIndex(item => item.id === edit_data.value.pid)
      const children_index = table_list.value[index].children.findIndex(item => item.id === response.data.id)
      table_list.value[index].children.splice(children_index, 1)
    }
    edit_data.value = JSON.parse(JSON.stringify(default_data))
  })
}
const deleteClick = () => {
  window.$box.confirm(
      '是否确认删除该路由？',
      '注意！删除后会影响系统运行！',
      {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning',
      }
  ).then(() => {
    AdminDelete()
  }).catch(() => {
  })
}
const editDoneClick = async () => {
  let response
  let data = JSON.parse(JSON.stringify(edit_data.value))
  if (data.id === 0) {
    response = await $api('AdminAdminAuthCreate', data)
  } else {
    data.password = 'placeholder'
    response = await $api('AdminAdminAuthUpdate', data)
  }
  $response(response, () => {
    edit_show.value = false
    table_ref.value.setCurrentRow(null)
    AdminAuthSelect()
    AdminAuthList()
    window.$message().success(data.id === 0 ? '创建成功' : '修改成功')
    edit_data.value = JSON.parse(JSON.stringify(default_data))
  })
}
const icon_show = ref(false)

const iconChooseClick = (icon) => {
  edit_data.value.icon = ''
  nextTick(() => {
    edit_data.value.icon = icon
  })
  icon_show.value = false
}

const iconClick = () => {
  icon_show.value = true
  icons_show.value = true
}
</script>
<template>
  <div>
    <el-drawer v-model="icon_show" title="图标选择">
      <div>
        <div class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">搜索</el-tag>
          </div>
          <div ml-2>
            <el-input class="input_line_input_wrapper" v-model="icons_search"
                      placeholder="请输入"></el-input>
          </div>
        </div>
        <div mt-2>
          <el-collapse v-for="(i,k) in icons_list">
            <el-collapse-item :title="i.nameCN" :name="i.name">
              <el-row>
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
    <el-dialog v-model="edit_show" :title="!!edit_data.id ? '编辑' : '新建'" width="500px"
               :close-on-click-modal="false"
               :close-on-press-escape="false"
               :show-close="false">
      <div>
        <div class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">名称</el-tag>
          </div>
          <div ml-2>
            <el-input class="input_line_input_wrapper" v-model="edit_data.title"
                      placeholder="请输入名称"></el-input>
          </div>
        </div>
        <div mt-2 class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">路由</el-tag>
          </div>
          <div ml-2>
            <el-input class="input_line_input_wrapper" v-model="edit_data.name"
                      placeholder="请输入路由"></el-input>
          </div>
        </div>
        <div mt-2 class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">图标</el-tag>
          </div>
          <div ml-2>
            <div @click="iconClick()" cursor-pointer class="input_line_input_wrapper icon_wrapper" text-center>
              <el-icon>
                <Icon v-if="!!edit_data.icon" :type="edit_data.icon"></Icon>
              </el-icon>
            </div>
          </div>
          <div ml-2>
            <el-button @click="edit_data.icon = ''" :disabled="!edit_data.icon">清空</el-button>
          </div>
        </div>
        <div mt-2 class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">分组</el-tag>
          </div>
          <div ml-2>
            <el-select v-model="edit_data.pid"
                       class="input_line_input_wrapper"
                       placeholder="请选择分组">
              <el-option v-for="(i,k) in [
                  {id:0,title:'根节点'},
                  ...admin_auth_select,
                ]" :key="k" :label="i.title" :value="i.id"/>
            </el-select>
          </div>
          <el-button ml-2 @click="AdminAuthSelect()" type="primary">刷新</el-button>
        </div>
        <div mt-2 class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">类型</el-tag>
          </div>
          <div ml-2>
            <el-select v-model="edit_data.type" class="input_line_input_wrapper"
                       placeholder="请选择">
              <el-option label="分组" :value="1"/>
              <el-option label="页面/接口" :value="2"/>
            </el-select>
          </div>
        </div>
        <div mt-2 class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">检查类型</el-tag>
          </div>
          <div ml-2>
            <el-select v-model="edit_data.check" class="input_line_input_wrapper"
                       placeholder="请选择">
              <el-option label="需要验证" :value="1"/>
              <el-option label="不需要验证" :value="2"/>
            </el-select>
          </div>
        </div>
        <div mt-2 class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">显示/隐藏</el-tag>
          </div>
          <div ml-2>
            <el-select v-model="edit_data.show" class="input_line_input_wrapper"
                       placeholder="请选择">
              <el-option label="显示" :value="1"/>
              <el-option label="隐藏" :value="2"/>
            </el-select>
          </div>
        </div>
        <div mt-2 class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">验证失败提示</el-tag>
          </div>
          <div ml-2>
            <el-input class="input_line_input_wrapper" v-model="edit_data.message"
                      placeholder="请输入验证失败提示"></el-input>
          </div>
        </div>
        <div mt-2 class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">排序</el-tag>
          </div>
          <div ml-2>
            <el-input-number class="input_line_input_wrapper" v-model="edit_data.order"
                             placeholder="请输入排序"></el-input-number>
          </div>
        </div>
        <div mt-2 class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">状态</el-tag>
          </div>
          <div ml-2>
            <el-select class="input_line_input_wrapper" v-model="edit_data.status" placeholder="请选择状态">
              <el-option label="可用" :value="1"/>
              <el-option label="停用" :value="2"/>
            </el-select>
          </div>
        </div>
      </div>
      <template #footer>
        <div class="dialog-footer">
          <el-button @click="edit_show = false">关闭</el-button>
          <el-button @click="editDoneClick()" type="primary">保存</el-button>
        </div>
      </template>
    </el-dialog>

    <el-card>
      <template #header>路由配置</template>
      <div>
        <div>
          <el-button @click="editClick(0)" type="primary">新建</el-button>
          <el-button :disabled="edit_data.id === 0" @click="editClick(1)" type="success">编辑</el-button>
          <el-button :disabled="edit_data.id === 0" @click="deleteClick()" type="danger">删除</el-button>
        </div>
        <el-table row-class-name="cursor-pointer" mt-2 border :data="table_list" style="width: 100%" row-key="id"
                  @row-click="tableRowClick" :ref="tableRef" highlight-current-row>
          <el-table-column label="名称" width="200">
            <template #default="scope">
              #{{ scope.row.id }} {{ scope.row.title }}
            </template>
          </el-table-column>
          <el-table-column prop="name" label="路由" width="200"/>
          <el-table-column label="图标" width="60">
            <template #default="scope">
              <el-icon>
                <Icon v-if="!!scope.row.icon" :type="scope.row.icon"></Icon>
              </el-icon>
            </template>
          </el-table-column>
          <el-table-column label="类型" width="120">
            <template #default="scope">
              <el-tag disable-transitions w-full :type="scope.row.type === 1 ? '' : 'success'">
                {{ scope.row.type === 1 ? '分组' : '页面/接口' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="检查类型" width="120">
            <template #default="scope">
              <el-tag disable-transitions w-full :type="scope.row.check === 1 ? 'warning' : 'success'">
                {{ scope.row.check === 1 ? '需要验证' : '不需要验证' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="显示/隐藏" width="100">
            <template #default="scope">
              <el-tag disable-transitions w-full :type="scope.row.show === 1 ? '' : 'warning'">
                {{ scope.row.show === 1 ? '显示' : '隐藏' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="状态" width="80">
            <template #default="scope">
              <el-tag disable-transitions w-full :type="scope.row.status === 1 ? '' : 'danger'">
                {{ scope.row.status === 1 ? '可用' : '停用' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="message" label="验证失败提示"/>
          <el-table-column prop="order" label="排序" width="80"/>
        </el-table>
      </div>
    </el-card>
  </div>
</template>
<style scoped>
.icon_wrapper {
  border: #dcdfe6 1px solid;
  box-sizing: border-box;
  border-radius: 4px;
  height: 30px;
  line-height: 30px;
}
</style>
<route>
{"meta":{"title":"路由配置"}}
</route>
