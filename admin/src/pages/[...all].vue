<script setup>
</script>
<template>
  <div class="page_404_wrapper">
    <div class="page_404_box_wrapper">
      <div class="page_404_svg_wrapper">
        <img class="page_404_svg_image_wrapper" :src="`./assets/svg/404.svg`" alt="">
      </div>
      <div class="page_404_text_wrapper">
        <div>未找到页面</div>
        <div ml-2>
          <el-button text type="primary" @click="$router.back()">返回</el-button>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.page_404_wrapper {
  background: #cccccc40;
  height: 100vh;
}

.page_404_box_wrapper {
  background: #ffffff;
  width: 800px;
  margin: 0 auto;
  box-shadow: 0 0 10px #cccccc;
  padding-bottom: 50px;
}

.page_404_svg_wrapper {
  width: 400px;
  margin: 0 auto;
}

.page_404_svg_image_wrapper {
  width: 400px;
  margin: 0 auto;
}

.page_404_text_wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
}
</style>
<route>
{"name":"404","meta":{"layout":"404"}}
</route>
