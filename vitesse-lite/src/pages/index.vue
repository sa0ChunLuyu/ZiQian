<script setup lang="ts" generic="T extends any, O extends any">
defineOptions({
  name: 'IndexPage',
})
const router = useRouter()
const go = () => {
  router.push(`/hi`)
}
</script>

<template>
  <div>
    <el-button @click="go()">YO</el-button>
  </div>
</template>
