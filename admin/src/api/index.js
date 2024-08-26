import {$post} from '~/tool/axios'
import {useConfig, useSessionToken, useSaveTokenType, useToken, useStore} from "~/store";
import $router from "~/router"

const $save_token_type = useSaveTokenType()
const $session_token = useSessionToken()
const $token = useToken()
const $config = useConfig()
export const $url = (url_key) => {
  const $store = useStore()
  if (url_key in $store.api) {
    return $store.api[url_key]
  } else {
    return ''
  }
}
export const $api = async (url_key, data = {}, opt = {}) => {
  const opt_data = {
    success: $config.value.api.success,
    error: $config.value.api.error,
    login: $config.value.api.login,
    loading: true,
    ...opt,
  }
  const $store = useStore()
  if (!(url_key in $store.api)) {
    window.$message().error(`接口不存在 [${url_key}]`)
    return false
  }
  let url = $store.api[url_key]
  for (let i in data) {
    if ($store.api[url_key].includes('{' + i + '}')) {
      url = url.replace('{' + i + '}', encodeURIComponent(data[i]))
    }
  }
  if ($store.api[url_key].includes('{client}') && !('client' in data)) {
    url = url.replace('{client}', window.CLIENT_TYPE)
  }
  return await $post({
    url,
    data
  }, opt_data)
}

export const $headers = () => {
  let $token
  if ($save_token_type.value === 'local') {
    $token = useToken()
  } else {
    $token = useSessionToken()
  }
  return {
    'Authorization': 'Bearer ' + $token.value
  }
}

export const $image = (path) => {
  const path_ret = ['http://', 'https://', ';base64,', './']
  for (let i = 0; i < path_ret.length; i++) {
    if (path.indexOf(path_ret[i]) !== -1) {
      return path
    }
  }
  return `${window.BASE_ASSETS}${path}`
}
export const $base64 = async (file) => {
  let reader = new FileReader()
  reader.readAsDataURL(file)
  return await new Promise(resolve => (reader.onloadend = () => resolve(reader.result)))
}
export const $response = (res, then, opt = {}, next = false) => {
  if (res) {
    const opt_data = {
      success: $config.value.api.success,
      error: $config.value.api.error,
      login: $config.value.api.login,
      loading: true,
      ...opt,
    }
    if (opt_data.login.includes(res.code)) {
      $session_token.value = null
      $token.value = null
      if (!!next) {
        next('/login')
      } else {
        $router.push('/login')
      }
    }
    if (res.code !== opt_data.success) return window.$message().error(res.message)
    then()
  }
}
