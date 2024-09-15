window.$message = () => {
  ElMessage.closeAll()
  return ElMessage
}
window.$box = ElMessageBox
let loading = null
window.$loading = () => {
  return {
    open: (text = '加载中...') => {
      loading = ElLoading.service({
        lock: true,
        text: text,
      })
    },
    close: () => {
      loading.close()
    },
  }
}
window.$open = (url, obj = {}, type = 'href') => {
  let urlString = url;
  if (Object.keys(obj).length > 0) {
    urlString += "?" + Object.entries(obj)
      .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(value)}`)
      .join("&");
  }
  switch (type) {
    case "href":
      window.location.href = urlString;
      break;
    case "open":
      window.open(urlString);
  }
}
