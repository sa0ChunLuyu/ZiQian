<script setup>
/**
 * name：后台配置
 * user：sa0ChunLuyu
 * date：2024年8月27日 11:45:09
 */
import {
  $api, $base64, $image,
  $response
} from '~/api'

const config_data = ref(false)
const getConfig = async () => {
  let config_arr = []
  form_array.value.forEach((item) => {
    item.forEach((i) => {
      config_arr.push(i.value)
    })
  })
  const response = await $api('AdminConfigGet', {config_arr})
  $response(response, () => {
    config_data.value = response.data
  })
}

const imageDeleteClick = (index) => {
  config_data.value[index] = ''
}

const fileChange = async (e, index) => {
  if (e.size > 1024 * 1024 * 2) return window.$message().error('图片大小不能超过2M')
  await UploadImage(await $base64(e.raw), index)
}
const UploadImage = async (base64, index) => {
  const response = await $api('AdminUploadImage', {
    base64
  })
  $response(response, () => {
    config_data.value[index] = response.data.url
  })
}
const form_array = ref([[{
  value: 'Logo',
  delete: false,
  width: 200,
  height: 200,
}, {
  value: 'Login欢迎图片',
  delete: false,
  width: 250,
  height: 225,
}, {
  value: '网站名称'
}, {
  value: 'Login背景色'
}], [{
  value: 'Favicon',
  delete: false,
  width: 200,
  height: 200,
}, {
  value: 'Login背景图',
  delete: false,
  width: 384,
  height: 216,
}, {
  value: '后台账号单点登录'
}, {
  value: '后台IP地区信息'
}, {
  value: '后台图形验证'
}]])

const predefine_config = [
  '#ff4500',
  '#ff8c00',
  '#ffd700',
  '#90ee90',
  '#00ced1',
  '#1e90ff',
  '#c71585',
  'rgba(255, 69, 0, 0.68)',
  'rgb(255, 120, 0)',
  'hsv(51, 100, 98)',
  'hsva(120, 40, 94, 0.5)',
  'hsl(181, 100%, 37%)',
  'hsla(209, 100%, 56%, 0.73)',
  '#c7158577',
]
const configEditClick = async (name) => {
  const response = await $api('AdminConfigEdit', {
    name,
    value: config_data.value[name]
  })
  $response(response, () => {
    window.$message().success('修改成功，刷新页面后生效')
    getConfig()
  })
}

onMounted(() => {
  getConfig()
})
</script>
<template>
  <div>
    <el-card>
      <template #header>后台配置</template>
      <div>
        <div v-if="!!config_data">
          <div class="config_form_wrapper">
            <div class="config_form_item_wrapper" v-for="(i,k) in form_array" :key="k">
              <el-form label-width="130px">
                <el-form-item v-for="(ii,ik) in i" :key="ik" :label="ii.value">
                  <template v-if="['Logo','Favicon','Login欢迎图片','Login背景图'].includes(ii.value)">
                    <div class="form_image_wrapper" :style="{
                      width: ii.width + 'px',
                      height: ii.height + 'px',
                    }">
                      <div v-if="!!config_data[ii.value] && !!ii.delete" class="form_image_delete_wrapper">
                        <el-button @click="imageDeleteClick(ii.value)" type="danger" size="small">
                          <Icon type="delete" :size="12"></Icon>
                        </el-button>
                      </div>
                      <el-upload :auto-upload="false" :show-file-list="false"
                                 @change="(e)=>{fileChange(e,ii.value)}">
                        <el-image :style="{
                      width: ii.width + 'px',
                      height: ii.height + 'px',
                    }" v-if="!!config_data[ii.value]" :src="$image(config_data[ii.value])" fit="contain"></el-image>
                        <div v-else :style="{
                      width: ii.width + 'px',
                      height: ii.height + 'px',
                      lineHeight: ii.height + 'px',
                    }" class="form_image_empty_wrapper">上传图片
                        </div>
                      </el-upload>
                    </div>
                  </template>
                  <template v-else-if="['Login背景色'].includes(ii.value)">
                    <el-color-picker :predefine="predefine_config" v-model="config_data[ii.value]" show-alpha/>
                  </template>
                  <template v-else-if="['后台图形验证','后台账号单点登录','后台IP地区信息'].includes(ii.value)">
                    <el-switch v-model="config_data[ii.value]" inline-prompt
                               style="--el-switch-on-color: #13ce66; --el-switch-off-color: #ff4949"
                               active-text="开启" inactive-text="关闭" :active-value="1" :inactive-value="0"/>
                  </template>
                  <template v-else>
                    <el-input class="form_input_wrapper" v-model="config_data[ii.value]"
                              placeholder="请输入"></el-input>
                  </template>
                  <el-button @click="configEditClick(ii.value)" ml-2 type="primary">保存</el-button>
                </el-form-item>
              </el-form>
            </div>
          </div>
        </div>
      </div>
    </el-card>
  </div>
</template>
<style scoped>
.config_form_wrapper {
  display: flex;
  justify-content: space-around;

  .config_form_item_wrapper {
    width: 45%;

    .form_input_wrapper {
      width: 300px;
    }

    .form_image_wrapper {
      background-image: linear-gradient(45deg, #eee 25%, transparent 25%, transparent 75%, #eee 75%), linear-gradient(45deg, #eee 25%, transparent 25%, transparent 75%, #eee 75%);
      background-size: 16px 16px;
      background-position: 0 0, 8px 8px;
      position: relative;

      .form_image_delete_wrapper {
        display: none;
        position: absolute;
        z-index: 999;
        top: 0;
        right: 5px;
      }

      .form_image_empty_wrapper {
        font-size: 14px;
        text-align: center;
      }
    }

    .form_image_wrapper:hover {
      .form_image_delete_wrapper {
        display: block;
      }
    }
  }
}
</style>
<route>
{"meta":{"title":"后台配置"}}
</route>