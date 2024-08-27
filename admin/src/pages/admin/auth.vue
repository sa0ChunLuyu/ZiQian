<script setup>
/**
 * name：权限管理
 * user：sa0ChunLuyu
 * date：2024年8月26日 19:31:26
 */
import {$api, $response} from '~/api'

const quick_database_ref = ref(null)
const table_list_active = computed(() => {
  return !!quick_database_ref.value ? quick_database_ref.value.table_list_active : []
})
const admin_auth = ref([])
const AdminAuthChoose = async () => {
  const response = await $api('AdminAdminAuthChoose')
  $response(response, () => {
    admin_auth.value = response.data.list.map((item) => {
      item.info.indeterminate = false
      item.info.check = false
      return item
    })
  })
}
const edit_show = ref(false)
const default_data = {
  id: 0,
  admin_auths: []
}
const edit_data = ref(JSON.parse(JSON.stringify(default_data)))
const editClick = async () => {
  if (admin_auth.value.length === 0) {
    await AdminAuthChoose()
  }
  if (!!quick_database_ref.value && quick_database_ref.value.table_list_active.length === 1) {
    edit_data.value = JSON.parse(JSON.stringify({
      id: quick_database_ref.value.table_list_active[0].id,
      admin_auths: JSON.parse(quick_database_ref.value.table_list_active[0].admin_auths)
    }))
    const adminAuthSet = new Set(edit_data.value.admin_auths)
    admin_auth.value.forEach((auth, index) => {
      let ids = auth.list.map(item => item.id)
      const is_subset = ids.every(item => adminAuthSet.has(item))
      const is_not_included = ids.every(item => !adminAuthSet.has(item))
      admin_auth.value[index].info.indeterminate = is_not_included ? false : !is_subset
      admin_auth.value[index].info.check = is_subset
    })
    edit_show.value = true
  }
}
const handleCheckAllChange = (e, scope) => {
  let ids = scope.row.list.map(item => item.id)
  const result = edit_data.value.admin_auths.filter((element) => !ids.includes(element))
  admin_auth.value[scope.$index].info.indeterminate = false
  if (e) {
    edit_data.value.admin_auths = [...result, ...ids]
  } else {
    edit_data.value.admin_auths = result
  }
}
const handleCheckedAuthChange = (e, scope) => {
  let adminAuths = edit_data.value.admin_auths
  let ids = scope.row.list.map(item => item.id)
  let is_subset = ids.every(item => adminAuths.includes(item))
  let is_not_included = ids.every(item => !adminAuths.includes(item))
  let adminAuth = admin_auth.value[scope.$index]
  adminAuth.info.indeterminate = is_not_included ? false : !is_subset
  adminAuth.info.check = is_subset
}

const editDone = async () => {
  const response = await $api('AdminAdminAuthGroupUpdate', {
    id: edit_data.value.id,
    admin_auths: JSON.stringify(edit_data.value.admin_auths)
  })
  $response(response, () => {
    edit_show.value = false
    quick_database_ref.value?.getDataList()
  })
}
</script>
<template>
  <el-dialog v-model="edit_show" title="编辑权限" width="800px"
             :close-on-click-modal="false"
             :close-on-press-escape="false"
             :show-close="false">
    <div>
      <div>
        <el-button size="small" @click="AdminAuthChoose()" type="primary">刷新</el-button>
      </div>
      <div mt-2 class="input_line_wrapper">
        <el-table border height="300" :data="admin_auth" style="width: 100%">
          <el-table-column property="name" label="类型" width="140">
            <template #default="scope">
              <el-checkbox
                  v-model="scope.row.info.check"
                  :indeterminate="scope.row.info.indeterminate"
                  @change="(e)=>{handleCheckAllChange(e,scope)}">
                {{ scope.row.info.title }}
              </el-checkbox>
            </template>
          </el-table-column>
          <el-table-column label="权限">
            <template #default="scope">
              <el-checkbox-group @change="(e)=>{handleCheckedAuthChange(e,scope)}" v-model="edit_data.admin_auths">
                <el-checkbox v-for="auth in scope.row.list" :key="auth.id" :value="auth.id">
                  {{ auth.title }}
                </el-checkbox>
              </el-checkbox-group>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </div>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="edit_show = false">关闭</el-button>
        <el-button @click="editDone()" type="primary">保存</el-button>
      </div>
    </template>
  </el-dialog>

  <div>
    <el-card>
      <template #header>权限管理</template>
      <div>
        <QuickDatabase ref="quick_database_ref" database="admin_auth_groups">
          <template v-slot:buttonLeft>
            <el-button @click="editClick()" :disabled="table_list_active.length !== 1" type="warning">
              编辑权限
            </el-button>
          </template>
        </QuickDatabase>
      </div>
    </el-card>
  </div>
</template>
<style scoped>

</style>
<route>
{"meta":{"title":"权限管理"}}
</route>
