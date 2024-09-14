<template></template>
<script setup>
import {ElLoading, ElMessage, ElMessageBox, ElNotification} from 'element-plus'
import {usePageSpy} from "~/store";

const $page_spy = usePageSpy()
const pageSpyCheck = () => {
  for (let i in $page_spy.value) {
    if (!!$page_spy.value[i].active) {
      const script = document.createElement('script');
      script.src = $page_spy.value[i].url + '/page-spy/index.min.js';
      script.crossOrigin = 'anonymous';
      script.onload = function () {
        window.$pageSpy = new PageSpy();
      };
      document.head.appendChild(script);
    }
  }
}
pageSpyCheck()

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
