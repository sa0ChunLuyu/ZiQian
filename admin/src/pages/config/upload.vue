<script setup>
/**
 * name：上传管理
 * user：sa0ChunLuyu
 * date：2024年8月26日 19:31:26
 */
import {$api, $base64, $response, $url} from "~/api";
import {$copy} from "~/tool/copy";
import CryptoJS from "crypto-js";

const table_list_active = computed(() => {
  return !!quick_database_ref.value ? quick_database_ref.value.table_list_active : []
})
const UploadDelete = async () => {
  const response = await $api('AdminUploadDelete', {
    id: table_list_active.value[0].id
  })
  $response(response, () => {
    window.$message().success('删除成功')
    quick_database_ref.value?.getDataList()
  })
}
const deleteClick = () => {
  window.$box.confirm(
      '是否确认删除该上传文件？',
      '提示',
      {
        confirmButtonText: '确认',
        cancelButtonText: '取消',
        type: 'warning',
      }
  ).then(() => {
    UploadDelete()
  }).catch(() => {
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
    quick_database_ref.value?.getDataList()
  })
}

const copyLinkClick = (link) => {
  $copy(link, () => {
    window.$message().success('复制成功')
  })
}

const upload_file_data = ref({
  token: '',
  token_time: 0,
  token_appid: '',
  token_noise: '',
  type: '',
  file_name: '',
})
const UploadToken = async (type, file) => {
  window.$loading().open('创建上传')
  const response = await $api('OpenWanLiuToken', {id: 1})
  window.$loading().close()
  $response(response, () => {
    upload_file_data.value = {
      ...upload_file_data.value,
      type: type,
      file_name: file.name,
      ...response.data,
    }
  })
}

const fileUploadSuccess = (response) => {
  window.$loading().close()
  $response(response, () => {
    quick_database_ref.value?.getDataList()
  })
}

const fileBeforeUpload = async (file) => {
  let max_size = 5
  if (file.size > 1024 * 1024 * max_size) {
    window.$message().error(`文件大小超过${max_size}M请使用分片上传`)
    return false
  }
  await UploadToken('File', file)
  window.$loading().open('上传中')
  return true
}

const chunk_upload_data = ref({
  count: 0,
  index: 0,
})
const chunk_upload_show = ref(false)
const progressFormat = (percentage) => {
  return percentage >= 100 ? '正在合并' : `${percentage}%`;
}

const chunkChange = async (file) => {
  let mix_size = 2
  if (file.size < 1024 * 1024 * mix_size) {
    window.$message().error(`文件大小少于${mix_size}M请使用文件上传`)
  } else {
    await chunkSlice(file)
  }
}
const chunkSlice = async (file) => {
  await UploadToken('Multipart', file)
  const file_size = file.size;
  const reader = new FileReader();
  reader.onload = async (event) => {
    const wordArray = CryptoJS.lib.WordArray.create(event.target.result);
    const md5 = CryptoJS.MD5(wordArray).toString();
    const chunk_size = 1024 * 1024;
    const chunks = Math.ceil(file_size / chunk_size);
    const file_chunks = [];
    for (let i = 0; i < chunks; i++) {
      const start = i * chunk_size;
      const end = Math.min(start + chunk_size, file_size);
      const chunk = file.raw.slice(start, end);
      file_chunks.push(chunk);
    }
    const formData = new FormData();
    for (let i in upload_file_data.value) {
      formData.append(i, upload_file_data.value[i]);
    }
    formData.append('file', file_chunks[0]);
    formData.append('md5', md5);
    formData.append('index', 1);
    window.$loading().open('正在创建分片')
    const response = await $api('OpenWanLiuUpload', formData, {
      loading: false
    })
    window.$loading().close()
    $response(response, () => {
      chunkAction(file, md5, file_chunks, response.data.list, 1)
    })
  };
  reader.readAsArrayBuffer(file.raw);
}
const quick_database_ref = ref(null)
const chunkAction = async (file, md5, file_chunks, success_list, index) => {
  chunk_upload_show.value = true
  chunk_upload_data.value.count = file_chunks.length
  if ((new Date().getTime() / 1000) - upload_file_data.value.token_time > (60 * 2)) {
    await UploadToken('Multipart', file)
  }
  chunk_upload_data.value.index = index
  if (index <= file_chunks.length) {
    if (success_list.indexOf(index) === -1) {
      const formData = new FormData();
      for (let i in upload_file_data.value) {
        formData.append(i, upload_file_data.value[i]);
      }
      formData.append('index', index);
      formData.append('md5', md5);
      formData.append('file', file_chunks[index - 1]);
      const response = await $api('OpenWanLiuUpload', formData, {
        loading: false
      })
      $response(response, () => {
        chunkAction(file, md5, file_chunks, response.data.list, index + 1)
      })
    } else {
      await chunkAction(file, md5, file_chunks, success_list, index + 1)
    }
  } else {
    const response = await $api('OpenWanLiuUpload', {
      ...upload_file_data.value,
      md5: md5,
      index: 'end',
    }, {
      loading: false
    })
    chunk_upload_show.value = false
    $response(response, () => {
      quick_database_ref.value?.getDataList()
    })
  }
}
</script>
<template>
  <el-dialog v-model="chunk_upload_show" title="分片上传" width="350px"
             :close-on-click-modal="false"
             :close-on-press-escape="false"
             :show-close="false">
    <div>
      <el-progress
          :format="progressFormat"
          :percentage="Math.min(parseFloat(((chunk_upload_data.index/chunk_upload_data.count)*100).toFixed(1)),100)"></el-progress>
    </div>
  </el-dialog>

  <div>
    <el-card>
      <template #header>上传管理</template>
      <div>
        <QuickDatabase ref="quick_database_ref" database="uploads">
          <template v-slot:buttonLeft>
            <el-dropdown>
              <el-button type="primary">
                上传
                <el-icon ml-2>
                  <Icon type="down"></Icon>
                </el-icon>
              </el-button>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item>
                    <el-upload accept="image/*" :auto-upload="false" :show-file-list="false"
                               @change="fileChange">
                      上传图片
                    </el-upload>
                  </el-dropdown-item>
                  <el-dropdown-item>
                    <el-upload :data="upload_file_data" :show-file-list="false"
                               :action="$url('OpenWanLiuUpload')" @success="fileUploadSuccess"
                               :before-upload="fileBeforeUpload">
                      <div>文件上传</div>
                    </el-upload>
                  </el-dropdown-item>
                  <el-dropdown-item>
                    <el-upload :auto-upload="false" :show-file-list="false" @change="chunkChange">
                      <div>分片上传</div>
                    </el-upload>
                  </el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
            <el-button ml-2 :disabled="table_list_active.length !== 1" @click="deleteClick()" type="danger">删除
            </el-button>
          </template>
        </QuickDatabase>
      </div>
    </el-card>
  </div>
</template>
<style scoped>

</style>
<route>
{"meta":{"title":"上传管理"}}
</route>
