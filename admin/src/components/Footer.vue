<script setup>
/**
 * name：
 * user：sa0ChunLuyu
 * date：2023年7月28日 19:27:47
 */
import {useConfig} from "~/store";

const $config = useConfig()
</script>
<template>
  <div>
    <div class="footer_wrapper">
      <div><el-text>{{ $config.version }}</el-text></div>
    </div>
  </div>
</template>
<style scoped>
.footer_wrapper {
  margin-top: 10px;
  text-align: center;
}
</style>

