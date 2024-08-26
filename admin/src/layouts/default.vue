<script setup>
import {useCollapsed} from "~/store";

const $collapsed = useCollapsed()
</script>
<template>
  <div>
    <el-container>
      <el-aside :width="!$collapsed ? '200px' : '64px'">
        <div class="aside_wrapper" :style="{
    width : !$collapsed ? '200px' : '64px'
    }">
          <Logo></Logo>
          <Menu></Menu>
        </div>
      </el-aside>
      <el-container>
        <el-header>
          <Header></Header>
        </el-header>
        <el-main p-0>
          <div class="main_wrapper" :style="{
    width : !$collapsed ? 'calc(100% - 210px)' : 'calc(100% - 74px)'
    }">
            <el-scrollbar height="100%">
              <div class="main_box_wrapper">
                <router-view v-slot="{ Component }">
                  <component :is="Component"/>
                </router-view>
              </div>
              <Footer></Footer>
            </el-scrollbar>
          </div>
        </el-main>
      </el-container>
    </el-container>
  </div>
</template>
<style scoped>
.main_box_wrapper {
  min-height: calc(100vh - 55px - 40px);
  position: relative;
}

.main_wrapper {
  position: fixed;
  top: 55px;
  bottom: 0;
  right: 0;
  background: #f0f2f5;
  padding: 5px;
  transition: width 0.3s ease;
}

.aside_wrapper {
  position: fixed;
  height: 100vh;
  border-right: 1px solid #ebeef5;
  box-sizing: border-box;
  overflow: hidden;
  transition: width 0.3s ease;
}
</style>
