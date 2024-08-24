<script setup>
</script>
<template>
  <div p="x4 y10" text="center blue-500 dark:gray-200">
    <div class="i404_wrapper">
      <div>404</div>
    </div>
    <div mt-2>未找到页面</div>
    <div mt-10>
      <el-button type="primary" @click="$router.back()">返回</el-button>
    </div>
  </div>
</template>
<style scoped>
.i404_wrapper {
  width: 300px;
  margin: 0 auto;
  font-size: 100px;
}

.i404_wrapper img {
  width: 300px;
}
</style>
<route>
{"name":"404","meta":{"layout":"404"}}
</route>
