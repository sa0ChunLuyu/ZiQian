<script setup>
/**
 * name：开发配置
 * user：sa0ChunLuyu
 * date：2024年8月27日 11:45:09
 */
</script>
<template>
  <div>
    <el-card>
      <template #header>开发配置</template>
      <div>
        <QuickDatabase database="configs">
          <template v-slot:header>
            <div class="mb-2">
              <el-alert title="以下配置轻易勿动！请在您已完全了解并知晓其配置具体含义之后再进行操作！！！" type="error" :closable="false"/>
            </div>
          </template>
        </QuickDatabase>
      </div>
    </el-card>
  </div>
</template>
<style scoped>

</style>
<route>
{"meta":{"title":"开发配置"}}
</route>