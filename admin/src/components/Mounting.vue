<template></template>
<script setup>
import {ElLoading, ElMessage, ElMessageBox, ElNotification} from 'element-plus'

window.$message = () => {
  ElMessage.closeAll()
  return ElMessage
}
window.$notification = () => {
  ElNotification.closeAll()
  return ElNotification
}
window.$box = ElMessageBox
let loading = null
window.$loading = (text = '加载中...') => {
  return {
    open: () => {
      loading = ElLoading.service({
        lock: true,
        text,
      })
    },
    close: () => {
      loading.close()
    },
  }
}
</script>
