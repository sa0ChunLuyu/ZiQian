<script setup>
/**
 * name：
 * user：sa0ChunLuyu
 * date：2024年8月31日 11:40:54
 */
import {$image} from '~/api'
import VueJsonPretty from "vue-json-pretty"
import {$copy} from "~/tool/copy"

const $props = defineProps({
  type: {
    type: String,
    default: 'unknown'
  },
  column: {
    type: Object,
    default: {}
  },
  row: {
    type: Object,
    default: {}
  },
  form: {
    type: Object,
    default: {}
  }
})

const value_view_content = ref('')
const value_view_type = ref('')
const value_view_show = ref(false)
const valueViewShow = (value, type) => {
  value_view_type.value = type
  switch (type) {
    case 'json':
      value_view_content.value = JSON.parse(value)
      break
    default:
      value_view_content.value = value
  }
  value_view_show.value = true
}

const valueShow = (data, column) => {
  switch (column.type) {
    case 'select':
      for (let i in column.select) {
        if (column.select[i].value === data) {
          data = column.select[i].label
          break
        }
      }
      break
    case 'json_array_count':
      data = JSON.parse(data).length
      break
  }
  if ('show' in column && !!column.show) {
    data = column.show.replace('{value}', data)
  }
  return data
}
const iframe_array = ['pdf', 'video', 'audio', 'txt', 'js', 'css', 'word', 'excel', 'ppt', 'jsonCode']
const office_array = ['word', 'excel', 'ppt']
const view_array = ['pdf', 'video', 'audio', 'txt', 'js', 'css', 'jsonCode']
const file_array = ['html', 'development']
const jump_array = []
const file_type_map = {
  image: '|png|jpg|jpeg|gif|bmp|svg|ico|',
  word: '|doc|docx|',
  excel: '|xlsx|xls|csv|',
  ppt: '|ppt|pptx|',
  pdf: '|pdf|',
  txt: '|txt|',
  audio: '|wav|mp3|',
  video: '|avi|mp4|flv|',
  js: '|js|',
  css: '|css|',
  html: '|html|',
  development: '|php|sql|',
  jsonCode: '|json|',
  rar: '|rar|zip|',
  apk: '|apk|',
  bat: '|bat|',
  exe: '|exe|',
  psd: '|psd|',
}
const fileType = (type) => {
  for (let i in file_type_map) {
    if (file_type_map[i].includes(`|${type}|`)) {
      return i
    }
  }
  return 'default'
}

const formType = (type, e) => {
  let value = type
  if (type.includes('bind:')) {
    for (let i in $props.form) {
      for (let ii in $props.form[i]) {
        if (ii === type.replace('bind:', '')) {
          return e[ii]
        }
      }
    }
  }
  return value
}
const fileClick = (url, type) => {
  if (office_array.includes(type)) {
    valueViewShow(`https://view.officeapps.live.com/op/view.aspx?src=${url}`, type)
  } else if (view_array.includes(type)) {
    valueViewShow(url, type)
  } else if (file_array.includes(type)) {
    window.$message().warning('暂不支持该文件预览')
  } else if (jump_array.includes(type)) {
    window.open(url)
  } else {
    window.$message().warning('暂不支持该文件预览')
  }
}
const columnButtonClick = () => {
  const action = $props.column.value.split(':')
  switch (action[0]) {
    case 'copy':
      let value = $props.row[action[2]]
      if (action[1] === 'image') {
        value = $image(value)
      }
      $copy(value, () => {
        window.$message().success('内容已复制')
      })
      break
    case 'valueView':
      valueViewShow($props.row[action[1]], $props.row[action[2]])
      break
  }
}
const value_show_scrollbar = ref(null)
const value_show_height = computed(() => {
  return !!value_show_scrollbar.value ? (value_show_scrollbar.value?.wrapRef.offsetHeight - 10) : 'auto'
})
</script>
<template>
  <el-dialog append-to-body v-model="value_view_show" title="内容查看" :width="1000" top="20px">
    <el-scrollbar v-if="!!value_view_show" ref="value_show_scrollbar" height="calc(100vh - 150px)">
      <div v-if="value_view_type === 'richText'" v-html="value_view_content"></div>
      <VueJsonPretty v-else-if="value_view_type === 'json'" :data="value_view_content"></VueJsonPretty>
      <iframe v-else-if="iframe_array.includes(value_view_type)" :src="value_view_content" border="0" width="100%"
              :height="value_show_height"></iframe>
      <div v-else>{{ value_view_content }}</div>
    </el-scrollbar>
  </el-dialog>
  <div>
    <div class="table_column_wrapper w-full" v-if="$props.type === 'stringArray'">
      <div w-full
           v-if="'tooltip' in $props.column && !!$props.column.tooltip && JSON.parse($props.row[$props.column.value]).join(' ').length > $props.column.tooltip">
        <el-tooltip effect="dark" :content="JSON.parse($props.row[$props.column.value]).join(' ')" placement="top">
          <div class="table_column_string_wrapper">{{ JSON.parse($props.row[$props.column.value]).join(' ') }}</div>
        </el-tooltip>
      </div>
      <div v-else class="table_column_string_wrapper">{{ JSON.parse($props.row[$props.column.value]).join(' ') }}</div>
    </div>
    <div class="table_column_wrapper w-full" v-else-if="$props.type === 'imageArray'">
      <div class="mr-2" v-for="(ii,ik) in JSON.parse($props.row[$props.column.value])">
        <el-image :key="ik" v-if="ik < 3" preview-teleported :initial-index="ik"
                  :preview-src-list="JSON.parse($props.row[$props.column.value]).map((item)=>$image(item))"
                  class="table_column_image_wrapper" :src="$image(ii)" fit="contain"></el-image>
      </div>
      <span v-if="JSON.parse($props.row[$props.column.value]).length > 3">...</span>
    </div>
    <div class="table_column_wrapper w-full" v-else-if="$props.type === 'image'">
      <el-image preview-teleported :previewSrcList="[$image($props.row[$props.column.value])]"
                class="table_column_image_wrapper" :src="$image($props.row[$props.column.value])"
                fit="contain"></el-image>
    </div>
    <div class="table_column_wrapper w-full" v-else-if="$props.type === 'json'">
      <el-button @click="valueViewShow($props.row[$props.column.value], 'json')" size="small" type="primary">查看
      </el-button>
    </div>
    <div class="table_column_wrapper w-full" v-else-if="$props.type === 'richText'">
      <el-button @click="valueViewShow($props.row[$props.column.value], 'richText')" size="small" type="primary">查看
      </el-button>
    </div>
    <div class="table_column_wrapper w-full" v-else-if="$props.type === 'switch'">
      <div class="table_column_switch_wrapper" :style="{
                  background: $props.row[$props.column.value] === '1' ? '#13ce66' : '#ff4949'
                }">{{ $props.row[$props.column.value] === '1' ? '开启' : '关闭' }}
      </div>
    </div>
    <div class="table_column_wrapper w-full" v-else-if="$props.type === 'color'">
      <div class="table_column_color_wrapper" :style="{
                  background: $props.row[$props.column.value]
                }"></div>
      <div w-full
           v-if="'tooltip' in $props.column && !!$props.column.tooltip && $props.row[$props.column.value].length > $props.column.tooltip">
        <el-tooltip effect="dark" :content="$props.row[$props.column.value]" placement="top">
          <div class="table_column_string_wrapper ml-2">{{ $props.row[$props.column.value] }}</div>
        </el-tooltip>
      </div>
      <div v-else class="table_column_string_wrapper ml-2">{{ $props.row[$props.column.value] }}</div>
    </div>
    <div class="table_column_wrapper w-full" v-else-if="$props.type === 'icon'">
      <Icon v-if="!!$props.row[$props.column.value]" :type="$props.row[$props.column.value]"></Icon>
    </div>
    <div class="table_column_wrapper w-full" v-else-if="$props.type === 'file'">
      <div v-if="fileType($props.row[$props.column.value.type]) === 'image'">
        <el-image preview-teleported :previewSrcList="[$image($props.row[$props.column.value.value])]"
                  class="table_column_file_image_wrapper" :src="$image($props.row[$props.column.value.value])"
                  fit="contain"></el-image>
      </div>
      <div v-else-if="fileType($props.row[$props.column.value.type]) !== 'default'">
        <img
            @click="fileClick($image($props.row[$props.column.value.value]),fileType($props.row[$props.column.value.type]))"
            class="table_column_file_icon_wrapper"
            :src="`./assets/svg/${fileType($props.row[$props.column.value.type])}.svg`" alt="">
      </div>
      <div v-else>
        <img @click="fileClick($image($props.row[$props.column.value.value]),'default')"
             class="table_column_file_icon_wrapper" :src="`./assets/svg/default.svg`"
             alt="">
      </div>
    </div>
    <div class="w-full" v-else-if="['string','select'].includes($props.type)">
      <div w-full
           v-if="'tooltip' in $props.column && !!$props.column.tooltip && valueShow($props.row[$props.column.value], $props.column).length > $props.column.tooltip">
        <el-tooltip effect="dark" :content="valueShow($props.row[$props.column.value], $props.column)" placement="top">
          <div class="table_column_string_wrapper">{{ valueShow($props.row[$props.column.value], $props.column) }}</div>
        </el-tooltip>
      </div>
      <div v-else class="table_column_string_wrapper">
        {{ valueShow($props.row[$props.column.value], $props.column) }}
      </div>
    </div>
    <div class="w-full" v-else-if="$props.type === 'desc'">
      <div>
        <el-descriptions direction="vertical" :column="$props.column.column" border>
          <div v-for="(i,k) in $props.column.value" :key="k">
            <el-descriptions-item :label="i.label" :span="!!i.span ? i.span : 1"
                                  :rowspan="!!i.rowspan ? i.rowspan : 1">
              <QuickDatabaseShow :type="formType(i.type, $props.row)" :column="{...$props.column,...i}"
                                 :row="$props.row"></QuickDatabaseShow>
            </el-descriptions-item>
          </div>
        </el-descriptions>
      </div>
    </div>
    <div class="flex w-full" v-else-if="$props.type === 'inline'">
      <div v-for="(i,k) in $props.column.value" :key="k" class="mr-1">
        <QuickDatabaseShow :type="formType(i.type, $props.row)" :column="{...$props.column,...i}"
                           :row="$props.row"></QuickDatabaseShow>
      </div>
    </div>
    <div v-else-if="$props.type === 'button'">
      <el-button @click="columnButtonClick()" :size="$props.column.button.size" :type="$props.column.button.type">
        {{ $props.column.label }}
      </el-button>
    </div>
    <div class="w-full" v-else>
      unknown {{ $props.type }} {{ $props.row[$props.column.value] }}
    </div>
  </div>
</template>
<style>
</style>
<style scoped>
.value_show_scrollbar_wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
}

.table_column_wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;

  .table_column_file_icon_wrapper {
    width: 40px;
    height: 40px;
    cursor: pointer;
  }

  .table_column_string_wrapper {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .table_column_image_wrapper {
    width: 40px;
    height: 40px;
  }

  .table_column_file_image_wrapper {
    width: 40px;
    height: 40px;
  }

  .table_column_color_wrapper {
    height: 20px;
    font-size: 14px;
    width: 20px;
    text-align: center;
    display: inline-block;
  }

  .table_column_switch_wrapper {
    height: 20px;
    line-height: 20px;
    font-size: 14px;
    width: 60px;
    text-align: center;
    color: #ffffff;
  }
}
</style>
