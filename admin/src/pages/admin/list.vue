<script setup>
/**
 * name：
 * user：sa0ChunLuyu
 * date：2023年8月10日 10:20:24
 */
import {
  $api,
  $image,
  $response,
  $base64
} from '~/api'
import $router from '~/router'
import {onBeforeRouteUpdate} from "vue-router";
import {$copy} from "~/tool/copy";

const default_page_options = {
  status: 0,
  search: '',
  admin_auth_group: 0,
  initial_password: 0,
  page: 1,
}
const page_options = ref(JSON.parse(JSON.stringify(default_page_options)))
onBeforeRouteUpdate((to) => {
  routerChange(to.query)
})
const admin_list = ref([])
const last_page = ref(0)
const AdminList = async () => {
  edit_data.value = JSON.parse(JSON.stringify(default_data))
  table_ref.value.setCurrentRow(null)
  const response = await $api('AdminAdminList', page_options.value)
  $response(response, () => {
    admin_list.value = response.data.list.data
    last_page.value = response.data.list.last_page
  })
}

const routerChange = (query) => {
  page_options.value = {
    status: Number(query.status) || default_page_options.status,
    search: query.search || default_page_options.search,
    admin_auth_group: Number(query.admin_auth_group) || default_page_options.admin_auth_group,
    initial_password: Number(query.initial_password) || default_page_options.initial_password,
    page: Number(query.page) || default_page_options.page,
  }
  AdminList()
}

onMounted(() => {
  routerChange($router.currentRoute.value.query)
  AdminAuthGroupSelect()
})
const admin_auth_group = ref([])
const AdminAuthGroupSelect = async () => {
  const response = await $api('AdminAdminAuthGroupSelect')
  $response(response, () => {
    admin_auth_group.value = response.data.list
  })
}
const searchClick = (page = 1) => {
  page_options.value.page = page
  $router.push({
    query: JSON.parse(JSON.stringify(page_options.value))
  })
}
const searchClearClick = () => {
  $router.push({
    query: JSON.parse(JSON.stringify(default_page_options))
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
  nickname: '',
  avatar: '',
  account: '',
  password: '',
  admin_auth_group: 0,
  initial_password: 1,
  status: 1,
}
const edit_data = ref(JSON.parse(JSON.stringify(default_data)))
const editClick = async (type) => {
  if (type === 0) {
    table_ref.value.setCurrentRow(null)
    edit_data.value = JSON.parse(JSON.stringify(default_data))
  }
  edit_show.value = true
}
const copy_show = ref(false)
const copy_data = ref(JSON.parse(JSON.stringify(default_data)))
const editDoneClick = async () => {
  let response
  let data = JSON.parse(JSON.stringify(edit_data.value))
  if (data.id === 0) {
    response = await $api('AdminAdminCreate', data)
  } else {
    data.password = 'placeholder'
    response = await $api('AdminAdminUpdate', data)
  }
  $response(response, () => {
    edit_show.value = false
    table_ref.value.setCurrentRow(null)
    AdminList()
    if (data.id === 0) {
      copy_data.value = JSON.parse(JSON.stringify(data))
      copy_show.value = true
    } else {
      window.$message().success('修改成功')
    }
    edit_data.value = JSON.parse(JSON.stringify(default_data))
  })
}
const AdminDelete = async () => {
  const response = await $api('AdminAdminDelete', {
    id: edit_data.value.id
  })
  $response(response, () => {
    window.$message().success('删除成功')
    edit_data.value = JSON.parse(JSON.stringify(default_data))
    table_ref.value.setCurrentRow(null)
    const index = admin_list.value.findIndex(item => item.id === response.data.id)
    admin_list.value.splice(index, 1)
  })
}
const deleteClick = () => {
  window.$box.confirm(
      '是否确认删除该管理员？',
      '提示',
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
const fileChange = async (e) => {
  if (e.size > 1024 * 1024 * 2) return window.$message().error('图片大小不能超过2M')
  await UploadImage(await $base64(e.raw))
}
const UploadImage = async (base64) => {
  const response = await $api('AdminUploadImage', {
    base64
  })
  $response(response, () => {
    edit_data.value.avatar = response.data.url
  })
}
const randomPasswordClick = () => {
  edit_data.value.password = generatePassword(12)
}
const generatePassword = (length) => {
  var charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789?.!@#$%&*()";
  var password = "";
  for (let i = 0; i < length; i++) {
    let randomIndex = Math.floor(Math.random() * charset.length);
    password += charset[randomIndex];
  }
  return password;
}
const copyClick = () => {
  $copy(`后台地址：${window.location.origin}${window.location.pathname}
账号：${copy_data.value.account}
密码：${copy_data.value.password}
${copy_data.value.initial_password === 1 ? '请务必' : '建议'}在登录后立即修改密码！`, () => {
    window.$message().success('内容已复制')
  })
}

const resetPasswordClick = () => {
  window.$box.confirm(
      '是否确认重置该管理员的密码？',
      '提示',
      {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning',
      }
  ).then(() => {
    AdminResetPassword()
  }).catch(() => {
  })
}

const AdminResetPassword = async () => {
  const response = await $api('AdminAdminResetPassword', {
    id: edit_data.value.id
  })
  $response(response, () => {
    copy_data.value = JSON.parse(JSON.stringify({
      ...edit_data.value,
      password: response.data.password
    }))
    copy_show.value = true
  })
}
</script>
<template>
  <div>
    <el-dialog v-model="copy_show" title="创建成功" width="500px"
               :close-on-click-modal="false"
               :close-on-press-escape="false"
               :show-close="false">
      <div>
        <div class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">账号</el-tag>
          </div>
          <div ml-2>{{ copy_data.account }}</div>
        </div>
        <div mt-2 class="input_line_wrapper">
          <div class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">密码</el-tag>
          </div>
          <div ml-2>{{ copy_data.password }}</div>
        </div>
      </div>
      <template #footer>
        <div class="dialog-footer">
          <el-button @click="copy_show = false">关闭</el-button>
          <el-button @click="copyClick()" type="primary">复制</el-button>
        </div>
      </template>
    </el-dialog>

    <el-dialog v-model="edit_show" :title="!!edit_data.id ? '编辑' : '新建'" width="500px"
               :close-on-click-modal="false"
               :close-on-press-escape="false"
               :show-close="false" top="30px">
      <div>
        <el-form label-position="top">
          <el-form-item label="账号">
            <el-input v-model="edit_data.account" placeholder="请输入账号"></el-input>
          </el-form-item>
          <el-form-item label="昵称">
            <el-input v-model="edit_data.nickname" placeholder="请输入昵称"></el-input>
          </el-form-item>
          <el-form-item label="头像">
            <el-upload :auto-upload="false" :show-file-list="false" @change="fileChange">
              <el-avatar :size="200" shape="square"
                         :src="$image(!!edit_data.avatar ? edit_data.avatar : '/storage/assets/default/avatar.png')"></el-avatar>
            </el-upload>
          </el-form-item>
          <el-form-item label="权限组">
            <div class="form_input_wrapper">
              <el-select :disabled="edit_data.id === 1" v-model="edit_data.admin_auth_group"
                         placeholder="请选择权限组">
                <el-option :disabled="i.id === -1" v-for="(i,k) in [
                  {id:-1,name:'超级管理员'},
                  {id:0,name:'暂不分配'},
                  ...admin_auth_group,
                ]" :key="k" :label="i.name" :value="i.id"/>
              </el-select>
            </div>
            <el-button ml-2 @click="AdminAuthGroupSelect()" type="primary">刷新</el-button>
          </el-form-item>
          <el-form-item label="密码">
            <div class="form_input_wrapper">
              <el-input v-model="edit_data.password" placeholder="请输入密码"></el-input>
            </div>
            <el-button ml-2 @click="randomPasswordClick()" type="primary">随机生成</el-button>
          </el-form-item>
          <el-form-item label="下次登录修改密码">
            <el-select v-model="edit_data.initial_password" placeholder="请选择">
              <el-option label="是" :value="1"/>
              <el-option label="否" :value="2"/>
            </el-select>
          </el-form-item>
          <el-form-item label="状态">
            <el-select v-model="edit_data.status" placeholder="请选择状态">
              <el-option label="可用" :value="1"/>
              <el-option label="停用" :value="2"/>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
      <template #footer>
        <div class="dialog-footer">
          <el-button @click="edit_show = false">关闭</el-button>
          <el-button @click="editDoneClick()" type="primary">保存</el-button>
        </div>
      </template>
    </el-dialog>

    <el-card>
      <template #header>人员列表</template>
      <div>
        <el-form :inline="true">
          <el-form-item label="昵称">
            <div class="form_input_wrapper">
              <el-input @keydown.enter="searchClick()" v-model="page_options.search"
                        placeholder="请输入昵称"></el-input>
            </div>
          </el-form-item>
          <el-form-item label="权限组">
            <div class="form_input_wrapper">
              <el-select v-model="page_options.admin_auth_group" placeholder="请选择权限组">
                <el-option v-for="(i,k) in [
                  {id:0,name:'全部'},
                  ...admin_auth_group,
                ]" :key="k" :label="i.name" :value="i.id"/>
              </el-select>
            </div>
          </el-form-item>
          <el-form-item label="状态">
            <div class="form_input_wrapper">
              <el-select v-model="page_options.status" placeholder="请选择状态">
                <el-option v-for="(i,k) in [
                  {id:0,name:'全部'},
                  {id:1,name:'可用'},
                  {id:2,name:'停用'}
                ]" :key="k" :label="i.name" :value="i.id"/>
              </el-select>
            </div>
          </el-form-item>
          <el-form-item label="密码">
            <div class="form_input_wrapper">
              <el-select v-model="page_options.initial_password" placeholder="请选择密码状态">
                <el-option v-for="(i,k) in [
                  {id:0,name:'全部'},
                  {id:1,name:'未修改'},
                  {id:2,name:'已修改'}
                ]" :key="k" :label="i.name" :value="i.id"/>
              </el-select>
            </div>
          </el-form-item>
          <el-form-item>
            <el-button @click="searchClick()" type="primary">搜索</el-button>
            <el-button @click="searchClearClick()">清空</el-button>
          </el-form-item>
        </el-form>
        <div mt-1>
          <el-button @click="editClick(0)" type="primary">新建</el-button>
          <el-button :disabled="edit_data.id === 0" @click="editClick(1)" type="success">编辑</el-button>
          <el-button :disabled="edit_data.id === 0" @click="deleteClick()" type="danger">删除</el-button>
          <el-button :disabled="edit_data.id === 0" @click="resetPasswordClick()" type="warning">重置密码</el-button>
        </div>
        <el-table row-class-name="cursor-pointer" mt-2 border :data="admin_list" highlight-current-row
                  style="width: 100%"
                  @row-click="tableRowClick" :ref="tableRef">
          <el-table-column label="昵称">
            <template #default="scope">
              <div class="table_item_line_wrapper">
                <el-avatar :size="40" shape="square"
                           :src="$image(!!scope.row.avatar ? scope.row.avatar : '/storage/assets/default/avatar.png')"></el-avatar>
                <span ml-2>{{ scope.row.nickname }}</span>
              </div>
            </template>
          </el-table-column>
          <el-table-column label="权限组" width="300">
            <template #default="scope">
              <el-tag disable-transitions :type="Number(scope.row.admin_auth_group) === -1 ? 'warning' : 'success'">
                <span v-if="Number(scope.row.admin_auth_group) === -1">超级管理员</span>
                <span v-else-if="!scope.row.admin_auth_group_name">未分配权限组</span>
                <span v-else>{{ scope.row.admin_auth_group_name }}</span>
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="密码状态" width="120">
            <template #default="scope">
              <el-tag disable-transitions :type="Number(scope.row.initial_password) === 1 ? 'warning' : 'success'">
                {{ Number(scope.row.initial_password) === 1 ? '未修改' : '已修改' }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column label="状态" width="120">
            <template #default="scope">
              <el-tag disable-transitions :type="Number(scope.row.status) === 1 ? 'success' : 'warning'">
                {{ Number(scope.row.status) === 1 ? '可用' : '停用' }}
              </el-tag>
            </template>
          </el-table-column>
        </el-table>
        <el-pagination v-if="last_page > 1" :current-page="page_options.page" mt-2 background layout="prev, pager, next"
                       :page-count="last_page" @update:current-page="searchClick"/>
      </div>
    </el-card>
  </div>
</template>
<style scoped>

</style>
<route>
{"meta":{"title":"人员列表"}}
</route>
