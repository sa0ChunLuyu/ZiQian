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
body {
  background: #ffffff;
}

* {
  outline: none;
}

.form_input_wrapper {
  width: 200px;
  display: flex;
  align-items: center;
}

.table_item_line_wrapper {
  display: flex;
  align-items: center;
}
</style>
