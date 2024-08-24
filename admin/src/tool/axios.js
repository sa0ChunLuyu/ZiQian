import axios from 'axios'
import {useToken, useStore, useSaveTokenType, useSessionToken} from '~/store'

const $save_token_type = useSaveTokenType()
let $token
const post = axios.create({
  method: 'POST'
})
post.interceptors.request.use(config => {
  if ('Authorization' in config.headers) {
    if (config.headers.Authorization === 'Delete Authorization') {
      delete config.headers['Authorization']
    }
  } else {
    if ($save_token_type.value === 'local') {
      $token = useToken()
    } else {
      $token = useSessionToken()
    }
    config.headers['Authorization'] = 'Bearer ' + $token.value
  }
  return config
}, error => Promise.reject(error))
post.interceptors.response.use(response => {
  return (response.status === 200) ? response : Promise.reject('[ERROR] response.status: ' + response.status)
}, error => Promise.reject(error))

export const $post = async (request, opt) => {
  const $store = useStore()
  if (opt.loading) {
    $store.loading++
    if ($store.loading === 1) window.$loading().open()
  }
  const response = await post(request).catch((e) => {
    window.$message().error(opt.error)
  })
  if (opt.loading) {
    $store.loading--
    if ($store.loading === 0) window.$loading().close()
  }
  return !!response ? response.data : false
}
