import {createRouter, createWebHashHistory} from 'vue-router'
import {setupLayouts} from 'virtual:generated-layouts'
import {routes} from 'vue-router/auto-routes'
import {
  useSaveTokenType, useSessionToken, useStore, useConfig, useToken, useLoginType, useRouterActive
} from '~/store'
import {$favicon} from "~/tool/favicon";
import {$api, $image, $response} from "~/api";
import {$post} from "~/tool/axios";

const router = createRouter({
  history: createWebHashHistory(import.meta.env.BASE_URL), routes: setupLayouts(routes)
})

const allow_unlogged_in_pages = ['/login', '/404'];
const $router_active = useRouterActive()
const updateRouterActive = (matched) => {
  matched.shift()
  const last = matched[matched.length - 1]
  if (allow_unlogged_in_pages.indexOf(last.name) !== -1) return
  setTimeout(() => {
    $router_active.value = matched.map((item) => {
      return {
        title: 'title' in item.meta ? item.meta.title : item.name,
        key: 'active' in item.meta ? item.meta.active : item.name,
      }
    })
  })
}
let error_status = false
let token_check = ''
router.beforeEach(async (to, from, next) => {
  const $store = useStore()
  const $login_type = useLoginType()
  if (!$store.api) {
    const $config = useConfig()
    if ($config.value.api.url.length > 0) {
      for (let i in $config.value.api.url) {
        if (!!$config.value.api.url[i].active) {
          window.BASE_URL = $config.value.api.url[i].url
          window.BASE_ASSETS = $config.value.api.url[i].assets
          window.CLIENT_TYPE = $config.value.api.url[i].type
          break
        }
      }
    }
    const api = await $post({url: window.BASE_URL}, {
      loading: false
    })
    if (api.code !== 200) {
      error_status = true
      window.$message().error('获取接口失败')
    } else {
      $store.api = api.data.list
    }
  }
  if (!error_status) {
    if (!$store.config) {
      const response = await $api('AdminConfigGet', {
        config_arr: [
          "Logo",
          "Favicon",
          "Login欢迎图片",
          "Login背景图",
          "Login背景色",
          "网站名称",
          "后台图形验证",
        ]
      }, {
        loading: false
      })
      $response(response, () => {
        $store.config = response.data
        $favicon($image(response.data['Favicon']))
      })
      if (!response) error_status = true
    }
  }
  if (!!error_status) {
    $store.config = {
      "Logo": "./assets/images/logo.png",
      "Favicon": "./assets/images/favicon.png",
      "Login欢迎图片": "./assets/images/login_cover.png",
      "Login背景图": "./assets/images/login_bg.png",
      "Login背景色": "#409eff",
      "网站名称": "网络错误",
      "网站介绍": "请点击右下角代理设置进行调整",
      "后台密码登录验证": "0",
    }
    $favicon($image($store.config['Favicon']))
    $login_type.value = 'proxy'
    if (to.name !== '/login') {
      next('/login')
    }
  }
  document.title = ('title' in to.meta && to.meta.title !== '首页') ? `${to.meta.title} ${$store.config['网站名称']}` : $store.config['网站名称']
  if (allow_unlogged_in_pages.includes(to.name)) {
    next()
  } else {
    const $save_token_type = useSaveTokenType()
    let $token;
    if ($save_token_type.value === 'local') {
      $token = useToken()
    } else {
      $token = useSessionToken()
    }
    if ($token.value === '') {
      $login_type.value = 'login'
      next('/login?f=' + encodeURIComponent(to.fullPath))
    } else {
      if (token_check === $token.value) {
        updateRouterActive(to.matched.map(item => item))
        next()
      } else {
        const response = await $api('AdminAdminStatus')
        $response(response, () => {
          token_check = $token.value
          updateRouterActive(to.matched.map(item => item))
          next()
        })
      }
    }
  }
})
export default router
