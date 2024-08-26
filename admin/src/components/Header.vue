<script setup>
/**
 * name：
 * user：sa0ChunLuyu
 * date：2023年7月27日 17:08:28
 */
import {useCollapsed, useIpNotification, useRouterActive, useStore} from "~/store";
import {getInfo} from "~/tool/info";
import {
  $api,
  $base64,
  $image,
  $response
} from '~/api'
import $router from '~/router'

const $store = useStore()
const $collapsed = useCollapsed()
onMounted(() => {
  checkUserInfo()
})
const checkUserInfo = () => {
  if (!$store.info) {
    getInfo((info) => {
      if (info.initial_password === 1) {
        changePasswordClick()
      } else {
        checkLastTimeIp()
      }
    })
  } else {
    if ($store.info.initial_password === 1) {
      changePasswordClick()
    } else {
      checkLastTimeIp()
    }
  }
}
const $ip_notification = useIpNotification()
const checkLastTimeIp = () => {
  if (!$ip_notification.value) {
    $ip_notification.value = true
    window.$notification()[
        $store.info.token_ip_info.ip !== $store.info.last_time_token_ip_info.ip ? 'warning' : 'success'
        ]({
      title: '登录信息',
      dangerouslyUseHTMLString: true,
      position: 'bottom-right',
      duration: $store.info.token_ip_info.ip !== $store.info.last_time_token_ip_info.ip ? 0 : 5000,
      message: `<div>
<b>本次登录地址:</b> ${$store.info.token_ip_info.ip}<br>
<b>本次登录信息:</b> ${$store.info.token_ip_info.region}<br>
<b>本次登录时间:</b> ${$store.info.token_ip_info.created_at}
</div>
` + ($store.info.token_ip_info.ip !== $store.info.last_time_token_ip_info.ip ? `<hr>
<div>
<b>上次登录地址:</b> ${$store.info.last_time_token_ip_info.ip}<br>
<b>上次登录信息:</b> ${$store.info.last_time_token_ip_info.region}<br>
<b>上次登录时间:</b> ${$store.info.last_time_token_ip_info.created_at}
</div>` : ''),
    })
  }
}

const quitClick = () => {
  window.$box.confirm(
      '确定要退出登录吗？',
      '提示',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
    $router.push('/login')
  }).catch(() => {
  })
}

const $router_active = useRouterActive()
const breadcrumb_show = computed(() => {
  return $router_active.value.map((item) => {
    return item
  })
})

const AdminAccountChangePassword = async () => {
  if (change_password_data.value.password !== change_password_data.value.check_password) {
    return window.$message().error('两次密码不一致')
  }
  const response = await $api('AdminAdminAccountChangePassword', {
    old_password: change_password_data.value.old_password,
    password: change_password_data.value.password,
    code: change_password_data.value.code,
    hash: change_password_data.value.hash,
    time: change_password_data.value.time,
    uuid: change_password_data.value.uuid,
  })
  $response(response, () => {
    window.$message().success('修改成功')
    changePasswordShowClose()
  })
}
const change_password_show = ref(false)
const change_password_data = ref({
  old_password: '',
  password: '',
  check_password: '',
  image: '',
  code: '',
  hash: '',
  time: '',
  uuid: '',
})
const changePasswordClick = () => {
  change_password_data.value = {
    old_password: '',
    password: '',
    check_password: '',
    image: '',
    code: '',
    hash: '',
    time: '',
    uuid: '',
  }
  change_password_show.value = true
  ImageCaptchaCreate()
}
const captcha_loading = ref(false)
const ImageCaptchaCreate = async () => {
  if (captcha_loading.value) return
  captcha_loading.value = true
  const response = await $api('AdminImageCaptchaCreate')
  captcha_loading.value = false
  $response(response, () => {
    response.data.old_password = response.data.password
    response.data.password = change_password_data.value.password
    change_password_data.value = {
      ...change_password_data.value,
      ...response.data
    }
  })
}
const changePasswordShowClose = () => {
  change_password_show.value = false
  window.location.reload()
}

const update_self_show = ref(false)
const update_self_data = ref({
  nickname: '',
  avatar: '',
})
const updateSelfClick = () => {
  update_self_data.value = JSON.parse(JSON.stringify({
    nickname: $store.info.nickname,
    avatar: $store.info.avatar,
  }))
  update_self_show.value = true
}
const AdminUpdateSelf = async () => {
  const response = await $api('AdminAdminUpdateSelf', update_self_data.value)
  $response(response, () => {
    update_self_show.value = false
    window.$message().success('修改成功')
    $store.info.nickname = update_self_data.value.nickname
    $store.info.avatar = update_self_data.value.avatar
  })
}
const fileChange = async (e) => {
  if (e.size > 1024 * 1024 * 2) return window.$message().error('图片大小不能超过2M')
  await UploadImage(await $base64(e.raw))
}
const UploadImage = async (base64) => {
  const response = await $api('AdminUploadImage', {
    base64
  })
  $response(response, () => {
    update_self_data.value.avatar = response.data.url
  })
}
const updateSelfShowClose = () => {
  update_self_show.value = false
  update_self_data.value = {
    nickname: '',
    avatar: '',
  }
}
</script>
<template>
  <div>
    <el-dialog v-model="update_self_show" title="编辑信息" width="350px"
               :close-on-click-modal="false"
               :close-on-press-escape="false"
               :show-close="false">
      <div>
        <el-form label-width="70">
          <el-form-item label="头像">
            <el-upload :auto-upload="false" :show-file-list="false" @change="fileChange">
              <el-avatar :size="200" shape="square"
                         :src="$image(!!update_self_data.avatar ? update_self_data.avatar : '/storage/assets/default/avatar.png')"></el-avatar>
            </el-upload>
          </el-form-item>
          <el-form-item label="昵称">
            <el-input v-model="update_self_data.nickname"
                      placeholder="请输入昵称"></el-input>
          </el-form-item>
        </el-form>
      </div>
      <template #footer>
        <div class="dialog-footer">
          <el-button @click="updateSelfShowClose()">关闭</el-button>
          <el-button @click="AdminUpdateSelf()" type="primary">保存</el-button>
        </div>
      </template>
    </el-dialog>

    <el-dialog v-model="change_password_show" title="修改密码" width="350px"
               :close-on-click-modal="false"
               :close-on-press-escape="false"
               :show-close="false">
      <div>
        <div v-if="$store.info.initial_password === 1">
          <el-alert :closable="false" center title="首次登录和重置密码后需要修改密码" type="error" effect="dark"/>
        </div>
        <div :class="[$store.info.initial_password === 1 ? 'mt-2' : '']">
          <el-form label-width="70">
            <el-form-item label="原密码">
              <el-input v-model="change_password_data.old_password"
                        placeholder="请输入原密码" type="password"></el-input>
            </el-form-item>
            <el-form-item label="新密码">
              <el-input v-model="change_password_data.password"
                        placeholder="请输入新密码" type="password"></el-input>
            </el-form-item>
            <el-form-item label="确认密码">
              <el-input v-model="change_password_data.check_password"
                        placeholder="请确认密码" type="password"></el-input>
            </el-form-item>
            <el-form-item label="验证码">
              <div w-full>
                <el-input v-model="change_password_data.code"
                          placeholder="请输入验证码"></el-input>
                <div v-loading="captcha_loading && !change_password_data.image"
                     @click="ImageCaptchaCreate()"
                     class="change_password_code_image_wrapper" mt-2>
                  <div w-full h-full v-if="!!change_password_data.image" :style="{
                    backgroundImage: 'url(' + $image(change_password_data.image) + ')'
                  }"></div>
                </div>
              </div>
            </el-form-item>
          </el-form>
        </div>
      </div>
      <template #footer>
        <div class="dialog-footer">
          <el-button @click="changePasswordShowClose()">关闭</el-button>
          <el-button @click="AdminAccountChangePassword()" type="primary">保存</el-button>
        </div>
      </template>
    </el-dialog>

    <div class="header_wrapper" :style="{
    width : !$collapsed ? 'calc(100% - 200px)' : 'calc(100% - 64px)'
    }">
      <div class="header_left_wrapper">
        <el-button @click="$collapsed = !$collapsed" text ml-1>
          <el-icon mr-1>
            <Icon v-if="!$collapsed" type="menu-fold"></Icon>
            <Icon v-else type="menu-unfold"></Icon>
          </el-icon>
        </el-button>
        <div class="header_breadcrumb_wrapper" ml-2>
          <el-breadcrumb>
            <el-breadcrumb-item :to="{ path: '/' }">
              {{ $store.config['网站名称'] }}
            </el-breadcrumb-item>
            <template v-if="$router_active[0].key !== 'index'">
              <el-breadcrumb-item v-for="(i,k) in breadcrumb_show" :key="k">
                {{ i.title }}
              </el-breadcrumb-item>
            </template>
          </el-breadcrumb>
        </div>
      </div>
      <div class="header_right_wrapper">
        <div v-if="!!$store.info" class="header_user_wrapper" ml-1 mr-2>
          <el-dropdown popper-class="no-border">
            <div>
              <el-button text>
                <el-avatar shape="square" :size="26"
                           :src="$image(!!$store.info.avatar? $store.info.avatar : '/storage/assets/default/avatar.png')"/>
                <div ml-2>
                  <el-text>{{ $store.info.nickname }}</el-text>
                </div>
              </el-button>
            </div>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item @click="updateSelfClick()">
                  <el-text>
                    <el-icon>
                      <Icon type="edit-name"></Icon>
                    </el-icon>
                    编辑资料
                  </el-text>
                </el-dropdown-item>
                <el-dropdown-item @click="changePasswordClick()">
                  <el-text>
                    <el-icon>
                      <Icon type="unlock"></Icon>
                    </el-icon>
                    修改密码
                  </el-text>
                </el-dropdown-item>
                <el-dropdown-item divided @click="quitClick()">
                  <el-text>
                    <el-icon>
                      <Icon type="power"></Icon>
                    </el-icon>
                    退出登录
                  </el-text>
                </el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </div>
    </div>
  </div>
</template>
<style>
.el-tooltip__trigger {
  outline: none;
}

.el-dropdown-link:focus-visible {
  outline: unset;
}
</style>
<style scoped>
.header_wrapper {
  position: fixed;
  top: 0;
  right: 0;
  height: 54px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  transition: width 0.3s ease;

  .header_right_wrapper,
  .header_left_wrapper {
    display: flex;
    align-items: center;
    height: 100%;
  }
}

.change_password_code_image_wrapper {
  width: 130px;
  height: 40px;
  background-repeat: no-repeat;
  background-size: cover;
  border: 1px solid #dcdfe6;
  box-sizing: border-box;
  overflow: hidden;
}
</style>
