<script setup>
/**
 * name：
 * user：sa0ChunLuyu
 * date：2023年7月27日 10:46:03
 */
import {
  useConfig,
  useIpNotification,
  useLoginType,
  useSaveTokenType,
  useSessionToken,
  useStore,
  useToken
} from "~/store";
import {$api, $image, $response} from "~/api";
import {onBeforeRouteUpdate} from "vue-router";
import $router from '~/router'
import {getInfo} from "~/tool/info";

const $ip_notification = useIpNotification()
const $store = useStore()
const $save_token_type = useSaveTokenType()
const $session_token = useSessionToken()
const $token = useToken()
const $config = useConfig()
const loading = ref(false)
const AdminLogin = async () => {
  const account_ = login_data.value.account.replace(/^\s+|\s+$/g, "")
  if (account_ === '') return window.$message().error('请输入账号')
  if (login_data.value.password === '') return window.$message().error('请输入密码')
  if (loading.value) return
  loading.value = true
  const response = await $api('AdminAdminLogin', {
    account: account_,
    password: login_data.value.password,
    code: login_data.value.code,
    hash: login_data.value.hash,
    time: login_data.value.time,
    uuid: login_data.value.uuid,
  }, {
    loading: false
  })
  loading.value = false
  $response(response, () => {
    const token = response.data.token
    $ip_notification.value = false
    saveToken(token)
  })
}
const login_data = ref({
  account: '',
  password: '',
  code: '',
  hash: '',
  time: '',
  uuid: ''
})
const default_page_options = {
  f: '/'
}
const page_options = ref(default_page_options)
onBeforeRouteUpdate((to) => {
  routerChange(to.query)
})
const getCaptchaCreate = () => {
  ImageCaptchaCreate()
}
const routerChange = (query) => {
  page_options.value = {
    f: query.f || default_page_options.f
  }
}

const captcha_loading = ref(false)
const ImageCaptchaCreate = async () => {
  if (captcha_loading.value) return
  captcha_loading.value = true
  const response = await $api('AdminImageCaptchaCreate', {}, {
    loading: false
  })
  captcha_loading.value = false
  $response(response, () => {
    login_data.value = {
      ...login_data.value,
      ...response.data
    }
  })
}
const local_token = ref(false)
const saveToken = (token) => {
  if (local_token.value) {
    $save_token_type.value = 'local'
    $session_token.value = ''
    $token.value = token
  } else {
    $save_token_type.value = 'session'
    $session_token.value = token
    $token.value = ''
  }
  getInfo()
  $router.push(decodeURIComponent(page_options.value.f))
}

const AdminQuit = async () => {
  if (!$token.value && !$session_token.value) return
  const response = await $api('AdminAdminQuit', {}, {
    loading: false
  })
  $response(response, () => {
    if ($save_token_type.value === 'local') {
      $token.value = ''
    } else {
      $session_token.value = ''
    }
  })
}

const $login_type = useLoginType()
const checkLoginType = () => {
  switch ($login_type.value) {
    case 'login':
      getCaptchaCreate()
      break
  }
}

const proxy_data_default = {
  id: String(new Date() / 1),
  active: false,
  name: '',
  url: '',
  assets: '',
  type: 'admin'
}
const proxy_data = ref(JSON.parse(JSON.stringify(proxy_data_default)))
const proxy_show = ref(false)
const createProxyClick = () => {
  proxy_data.value = JSON.parse(JSON.stringify({
    ...proxy_data_default,
    id: String(new Date() / 1),
  }))
  proxy_show.value = true
}
const updateProxyClick = (info) => {
  proxy_data.value = JSON.parse(JSON.stringify(info))
  proxy_show.value = true
}

const changeProxyActiveClick = (index) => {
  $config.value.api.url[index].active = !$config.value.api.url[index].active
  for (let i in $config.value.api.url) {
    if (Number(i) !== index) {
      $config.value.api.url[i].active = false
    }
  }
}

const reloadLoginPage = () => {
  $login_type.value = 'login'
  window.location.reload()
}

const deleteProxyClick = (index) => {
  window.$box.confirm(
      '是否确认删除该配置？',
      '提示',
      {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning',
      }
  ).then(() => {
    $config.value.api.url.splice(index, 1)
  }).catch(() => {
  })
}

const proxyEditDone = () => {
  if (proxy_data.value.name === '') return window.$message().error('请输入名称')
  if (proxy_data.value.url === '') return window.$message().error('请输入接口地址')
  if (proxy_data.value.assets === '') return window.$message().error('请输入资源地址')
  if (proxy_data.value.type === '') return window.$message().error('请输入类型')
  let index = -1
  for (let i in $config.value.api.url) {
    if ($config.value.api.url[i].id === proxy_data.value.id) {
      index = i
      break
    }
  }
  if (index === -1) {
    $config.value.api.url.push(proxy_data.value)
  } else {
    $config.value.api.url[index] = JSON.parse(JSON.stringify(proxy_data.value))
  }
  proxy_show.value = false
}

const proxyClick = () => {
  $login_type.value = 'proxy'
}

onMounted(() => {
  routerChange($router.currentRoute.value.query)
  checkLoginType()
  AdminQuit()
})
</script>
<template>
  <el-dialog v-model="proxy_show" title="代理" width="500">
    <div>
      <el-form label-width="70">
        <el-form-item label="名称">
          <el-input v-model="proxy_data.name" placeholder="请输入名称"></el-input>
        </el-form-item>
        <el-form-item label="类型">
          <el-input v-model="proxy_data.type" placeholder="请输入类型"></el-input>
        </el-form-item>
        <el-form-item label="接口地址">
          <el-input v-model="proxy_data.url" placeholder="请输入接口地址"></el-input>
        </el-form-item>
        <el-form-item label="资源地址">
          <el-input v-model="proxy_data.assets" placeholder="请输入资源地址"></el-input>
        </el-form-item>
      </el-form>
    </div>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="proxy_show = false">取消</el-button>
        <el-button type="primary" @click="proxyEditDone()">确认</el-button>
      </div>
    </template>
  </el-dialog>
  <div class="login_wrapper" :style="{
      backgroundImage: 'url(' + $image($store.config['Login背景图']) + ')'
    }">
    <el-card :body-style="{ padding: '0px' }">
      <div v-if="$login_type === 'proxy'" class="login_card_wrapper" p-5>
        <div w-full>
          <div class="proxy_button_wrapper">
            <el-button @click="reloadLoginPage()" text type="primary">重新启动</el-button>
            <el-button @click="createProxyClick()" text type="primary">新建代理</el-button>
          </div>
          <div class="proxy_table_wrapper">
            <el-table :data="$config.api.url" border style="width: 100%" height="calc(100%)" size="small">
              <el-table-column label="" width="30">
                <template #default="scope">
                  <div class="proxy_checkbox_wrapper">
                    <div @click="changeProxyActiveClick(scope.$index)" class="proxy_checkbox_cover_wrapper"></div>
                    <el-checkbox v-model="scope.row.active"></el-checkbox>
                  </div>
                </template>
              </el-table-column>
              <el-table-column prop="name" label="名称" width="80"/>
              <el-table-column prop="type" label="类型" width="80"/>
              <el-table-column prop="url" label="接口地址"/>
              <el-table-column prop="assets" label="资源地址"/>
              <el-table-column label="操作" width="180">
                <template #default="scope">
                  <div>
                    <el-button size="small" @click="updateProxyClick(scope.row)" text type="primary">编辑</el-button>
                    <el-button size="small" @click="deleteProxyClick(scope.$index)" text type="danger">删除</el-button>
                  </div>
                </template>
              </el-table-column>
            </el-table>
          </div>
        </div>
      </div>
      <div v-if="$login_type === 'login'" class="login_card_wrapper">
        <div class="login_cover_wrapper" :style="{
            backgroundImage: 'url(' + $image($store.config['Login欢迎图片']) + ')',
            backgroundColor: $store.config['Login背景色']
          }">
        </div>
        <div>
          <div v-loading="loading" class="login_form_wrapper">
            <div class="login_form_title_wrapper">登录</div>
            <div class="login_form_box_wrapper">
              <el-form>
                <el-form-item>
                  <el-input v-model="login_data.account"
                            placeholder="请输入登录账号">
                    <template #prefix>
                      <el-icon>
                        <Icon type="people"></Icon>
                      </el-icon>
                    </template>
                  </el-input>
                </el-form-item>
                <el-form-item>
                  <el-input type="password" v-model="login_data.password"
                            placeholder="请输入登录密码">
                    <template #prefix>
                      <el-icon>
                        <Icon type="lock"></Icon>
                      </el-icon>
                    </template>
                  </el-input>
                </el-form-item>
                <el-form-item>
                  <div class="code_wrapper">
                    <div class="code_input_wrapper">
                      <el-input w-full v-model="login_data.code"
                                placeholder="请输入验证码">
                        <template #prefix>
                          <el-icon>
                            <Icon type="protect"></Icon>
                          </el-icon>
                        </template>
                      </el-input>
                    </div>
                    <div v-loading="captcha_loading && !login_data.image"
                         class="code_image_wrapper" @click="ImageCaptchaCreate()">
                      <div w-full h-full v-if="!!login_data.image" :style="{
                    backgroundImage: 'url(' + $image(login_data.image) + ')'
                  }"></div>
                    </div>
                  </div>
                </el-form-item>
              </el-form>
              <div class="login_checkbox_wrapper">
                <el-checkbox v-model="local_token"/>
                <div ml-2>记住密码</div>
              </div>
              <div text-center mt-4>
                <el-button v-loading="loading" class="login_button_wrapper"
                           type="primary" @click="AdminLogin()">登录
                </el-button>
              </div>
              <div mt-10>
                <el-divider>便捷功能</el-divider>
              </div>
              <div class="login_footer_button_wrapper">
                <div mx-1>
                  <el-tooltip effect="dark" content="代理设置" placement="top">
                    <el-button @click="proxyClick()" text>
                      <Icon type="link-four"></Icon>
                    </el-button>
                  </el-tooltip>
                </div>
                <div mx-1>
                  <el-tooltip effect="dark" content="调试设置" placement="top">
                    <el-button text>
                      <Icon type="lightning"></Icon>
                    </el-button>
                  </el-tooltip>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </el-card>
  </div>
</template>
<style scoped lang="scss">
.login_wrapper {
  min-height: 100vh;
  background-repeat: no-repeat;
  background-position: center;
  background-size: cover;
  display: flex;
  justify-content: center;
  align-items: center;

  .login_card_wrapper {
    width: 920px;
    height: 450px;
    display: flex;

    .proxy_table_wrapper {
      width: 100%;
      margin-top: 10px;
      height: calc(100% - 40px);

      .proxy_checkbox_wrapper {
        position: relative;
        cursor: pointer;

        .proxy_checkbox_cover_wrapper {
          position: absolute;
          top: 0;
          bottom: 0;
          left: 0;
          right: 0;
          background: #00000000;
          z-index: 2;
        }
      }
    }

    .login_checkbox_wrapper {
      display: flex;
      align-items: center;
      font-size: 14px;
      margin-top: 15px;
    }

    .login_button_wrapper {
      width: 100%;
      height: 40px;
      margin: 0 auto;
    }

    .login_form_wrapper {
      width: 420px;
      height: 450px;
      overflow: hidden;

      .login_form_title_wrapper {
        font-size: 24px;
        color: #1d1d1f;
        margin-top: 35px;
        margin-left: 40px;
      }

      .login_form_box_wrapper {
        width: calc(100% - 80px);
        margin: 30px auto 0;

        .login_footer_button_wrapper {
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .code_wrapper {
          display: flex;
          align-items: center;
          width: 100%;

          .code_input_wrapper {
            width: calc(100% - 130px - 5px);
          }

          .code_image_wrapper {
            width: 130px;
            height: 32px;
            margin-top: 1px;
            background-repeat: no-repeat;
            background-size: cover;
            box-sizing: border-box;
            overflow: hidden;
            margin-left: 5px;
            transition: var(--el-transition-box-shadow);
            transform: translate3d(0, 0, 0);
            border: 1px var(--el-border-color) solid;
            cursor: pointer;

            div {
              background-repeat: no-repeat;
              background-position: center;
              background-size: cover;
            }
          }
        }
      }
    }

    .login_cover_wrapper {
      width: 500px;
      height: 450px;
      text-align: center;
      background-repeat: no-repeat;
      background-position: center;
      background-size: cover;
      overflow: hidden;
    }
  }
}
</style>
<route>
{"meta":{"title":"登录","layout":"404"}}
</route>

