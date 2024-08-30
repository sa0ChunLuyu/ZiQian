<script setup>
/**
 * name：
 * user：sa0ChunLuyu
 * date：2023年7月28日 08:08:35
 */
import {$api, $response} from '~/api'
import {useCollapsed, useRouterActive} from "~/store";
import $router from '~/router'

const $collapsed = useCollapsed()
const menu_list = ref([])
const AdminAuthMenu = async () => {
  const response = await $api('AdminAdminAuthMenu')
  $response(response, () => {
    menu_list.value = response.data.list
  })
}
onMounted(() => {
  if (!menu_list.value.length) AdminAuthMenu()
})

const $router_active = useRouterActive()
const default_active = computed(() => {
  return $router_active.value[$router_active.value.length - 1].key
})
const menuItemClick = (name) => {
  try {
    if (name.indexOf('open:') === 0) return window.open(name.replace('open:', ''))
    if (name.indexOf('link:') === 0) return window.location.href = name.replace('link:', '')
    $router.push({
      name
    })
  } catch (e) {
    $router.push({
      name: '/'
    })
  }
}
</script>
<template>
  <div>
    <div class="aside_menu_wrapper">
      <el-scrollbar height="calc(100vh - 56px)">
        <el-menu @select="menuItemClick" :default-active="default_active" :collapse="$collapsed"
                 background-color="#ffffff00" v-if="!!menu_list.length">
          <el-menu-item index="/">
            <el-icon>
              <Icon type="home"></Icon>
            </el-icon>
            <span>首页</span>
          </el-menu-item>
          <template v-for="(i,k) in menu_list">
            <el-sub-menu v-if="i.children.length > 1" :index="i.name" :disabled="i.status === 2">
              <template #title>
                <el-icon>
                  <Icon v-if="!!i.icon" :type="i.icon"></Icon>
                  <Icon v-else type="dot"></Icon>
                </el-icon>
                <span>{{ i.title }}</span>
              </template>
              <el-menu-item v-for="(ii,kk) in i.children" :index="ii.name" :key="kk" :disabled="ii.status === 2">
                <el-icon>
                  <Icon v-if="!!ii.icon" :type="ii.icon"></Icon>
                  <Icon v-else type="dot"></Icon>
                </el-icon>
                <span>{{ ii.title }}</span>
              </el-menu-item>
            </el-sub-menu>
            <el-menu-item v-if="i.children.length === 1" :index="i.children[0].name"
                          :disabled="i.status === 2 || i.children[0].status === 2">
              <el-icon>
                <Icon v-if="!!i.children[0].icon" :type="i.children[0].icon"></Icon>
                <Icon v-else type="dot"></Icon>
              </el-icon>
              <span>{{ i.children[0].title }}</span>
            </el-menu-item>
            <el-menu-item v-if="i.children.length === 0" :index="i.name" :disabled="i.status === 2">
              <el-icon>
                <Icon :type="i.icon"></Icon>
              </el-icon>
              <span>{{ i.title }}</span>
            </el-menu-item>
          </template>
        </el-menu>
      </el-scrollbar>
    </div>
  </div>
</template>
<style>
.aside_menu_wrapper .el-menu-item, .aside_menu_wrapper .el-sub-menu__title {
  height: 50px !important;
}

.aside_menu_wrapper .el-menu-item.is-active {
  background: #ecf3fd;
}

.aside_menu_wrapper .el-sub-menu.is-active .el-sub-menu__title {
  color: var(--el-menu-active-color);
}

.aside_menu_wrapper .el-menu {
  border: none;
}
</style>
<style scoped>
.menu_item_text_wrapper {
  margin-left: 10px;
}

.menu_item_icon_wrapper {
  display: inline-block;
  height: 100%;
}

.aside_menu_wrapper {
  border-top: 1px dashed #ebeef5;
  margin-top: 55px;
}
</style>

