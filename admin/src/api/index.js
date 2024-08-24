import {$post} from '~/tool/axios'
import {useConfig, useSessionToken, useSaveTokenType, useToken, useStore} from "~/store";
import $router from "~/router"

const $save_token_type = useSaveTokenType()
const $session_token = useSessionToken()
const $token = useToken()
const $config = useConfig()
export const $url = (url_key) => {
  const $store = useStore()
  if (url_key in $store.api_map) {
    return $store.api_map[url_key]
  } else {
    return ''
  }
}
export const $api = async (url_key, data = {}, opt = {}) => {
  const opt_data = {
    ...$config.value,
    ...opt,
  }
  const $store = useStore()
  if (!(url_key in $store.api_map)) {
    const api_map = await $post({url: opt_data.api_map_url}, opt_data)
    if (api_map.code !== 200) {
      window.$message().error('获取接口失败')
      return false
    }
    $store.api_map = api_map.data.list
  }
  if (!(url_key in $store.api_map)) {
    window.$message().error(`接口不存在 [${url_key}]`)
    return false
  }
  let url = $store.api_map[url_key]
  for (let i in data) {
    if ($store.api_map[url_key].indexOf('${' + i + '}') !== -1) {
      url = url.replace('${' + i + '}', encodeURIComponent(data[i]))
    }
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
  const path_ret = ['http://', 'https://', ';base64,']
  for (let i = 0; i < path_ret.length; i++) {
    if (path.indexOf(path_ret[i]) !== -1) {
      return path
    }
  }
  const $config = getApiActive()
  return `${$config.base_assets_url}${path}`
}
export const $base64 = async (file) => {
  let reader = new FileReader()
  reader.readAsDataURL(file)
  return await new Promise(resolve => (reader.onloadend = () => resolve(reader.result)))
}
export const $response = (res, then, opt = {}, next = false) => {
  if (res) {
    const opt_data = {
      ...$config.value,
      ...opt,
    }
    if ($config.value.should_login_code.indexOf(res.code) !== -1) {
      $session_token.value = null
      $token.value = null
      if (!!next) {
        next('/login')
      } else {
        $router.push('/login')
      }
    }
    if (res.code !== opt_data.success_code) return window.$message().error(res.message)
    then()
  }
}
