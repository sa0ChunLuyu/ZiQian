<script setup>
/**
 * name：
 * user：sa0ChunLuyu
 * date：2023年7月27日 17:08:28
 */
import {useCollapsed, useStore} from "~/store";
import {$image, $response} from '~/api'
import $router from '~/router'

const $store = useStore()
const $collapsed = useCollapsed()
</script>
<template>
  <div>
    <div>
      <div class="aside_logo_wrapper" :style="{
    width : !$collapsed ? '200px' : '64px'
    }">
        <div @click="$router.push('/')" class="aside_logo_cover_wrapper"></div>
        <el-menu
          :collapse="$collapsed"
          default-active="2"
          background-color="#ffffff00"
        >
          <el-menu-item index="1-1">
            <el-icon>
              <el-avatar style="background: #00000000;" class="aside_logo_avatar_wrapper" shape="square" :size="30"
                         :src="$image($store.config['Logo'])"/>
            </el-icon>
            <span class="aside_logo_title_wrapper">
              <el-text tag="b">{{ $store.config['网站名称'] }}</el-text>
            </span>
          </el-menu-item>
        </el-menu>
      </div>
    </div>
  </div>
</template>
<style>
.aside_logo_wrapper > .el-menu-item, .aside_logo_wrapper > .el-sub-menu__title {
  height: 54px !important;
}

.aside_logo_wrapper > .el-menu {
  border: none;
}
</style>
<style scoped>
.aside_logo_title_wrapper {
  text-align: center;
  width: 100%;
}

.aside_logo_avatar_wrapper {
  flex-shrink: 0;
}

.aside_logo_cover_wrapper {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: #00000000;
  z-index: 1;
  cursor: pointer;
}

.aside_logo_wrapper {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 54px;
  transition: width 0.3s ease;
}
</style>
