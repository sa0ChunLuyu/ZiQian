<script setup>
import zhCn from 'element-plus/dist/locale/zh-cn.mjs'

const button_config = {
  autoInsertSpace: true,
}
</script>
<template>
  <Mounting/>
  <el-config-provider :button="button_config" :locale="zhCn">
    <router-view/>
  </el-config-provider>
</template>
<style>
</style>
