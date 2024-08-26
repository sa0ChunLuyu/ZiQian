<script setup>/**
 * name：
 * user：sa0ChunLuyu
 * date：2023年8月10日 10:20:24
 */
import {
  $api,
  $response,
  $image
} from '~/api'
import $router from '~/router'
import {onBeforeRouteUpdate} from "vue-router";
import VueJsonPretty from 'vue-json-pretty';
import {$copy} from "~/tool/copy";

const default_page_options = {
  search: '',
  time: [null, null],
  method: '',
  code: '',
  page: 1,
}
const page_options = ref(JSON.parse(JSON.stringify(default_page_options)))
onBeforeRouteUpdate((to) => {
  routerChange(to.query)
})
const table_list = ref([])
const last_page = ref(0)
const RequestLogList = async () => {
  const response = await $api('AdminRequestLogList', page_options.value)
  $response(response, () => {
    table_list.value = response.data.list.data
    last_page.value = response.data.list.last_page
    page_options.value.time = response.data.time
  })
}

const routerChange = (query) => {
  page_options.value = {
    search: query.search || default_page_options.search,
    time: !!query.time && query.time !== 'null' ? JSON.parse(query.time) : default_page_options.time,
    method: query.method || default_page_options.method,
    code: query.code || default_page_options.code,
    page: Number(query.page) || default_page_options.page,
  }
  RequestLogList()
}

onMounted(() => {
  routerChange($router.currentRoute.value.query)
})
const searchClick = (page = 1) => {
  page_options.value.page = page
  let query = JSON.parse(JSON.stringify(page_options.value))
  query.time = JSON.stringify(query.time)
  $router.push({query})
}
const searchClearClick = () => {
  let query = JSON.parse(JSON.stringify(default_page_options))
  query.time = JSON.stringify(query.time)
  $router.push({query})
}
const json_show = ref(false)
const json_str = ref({})
const jsonShowClick = (data) => {
  json_str.value = data
  json_show.value = true
}

const copyClick = () => {
  $copy(JSON.stringify(json_str.value, null, 2), () => {
    window.$message().success('内容已复制')
  })
}

const txtShowClick = async (data) => {
  const response = await $api('AdminRequestLogTxt', data)
  $response(response, () => {
    jsonShowClick(JSON.parse(response.data.data))
  })
}

const cUrlJsonCopyClick = async (data) => {
  const response = await $api('AdminRequestLogTxt', data)
  $response(response, () => {
    cUrlCopyClick(JSON.parse(response.data.data))
  })
}

const cUrlCopyClick = (request) => {
  let requestUrl = $image(request.url);
  let requestType = request.method;
  let requestParams = request.params;
  let requestHeaders = request.header;
  let rawJson = JSON.stringify(request.input);
  let response = request.result;
  let cURL = `curl -X ${requestType} '${requestUrl}' \\\n`;
  Object.keys(requestHeaders).forEach(function (key) {
    if (key === 'content-type') {
      if (!requestHeaders[key]) {
        requestHeaders[key] = 'application/json'
      }
    }
    cURL += `-H '${key}: ${requestHeaders[key]}' \\\n`;
  });
  if (requestType === "GET") {
    let queryParams = Object.keys(requestParams)
        .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(requestParams[key])}`)
        .join("&");
    cURL += `'${requestUrl}?${queryParams}' \\\n`;
  }
  cURL += `-d '${rawJson}' \\\n`;
  cURL += `-w 'Response Code: 200\nResponse Data: ${JSON.stringify(response)}'`;
  $copy(cURL, () => {
    window.$message().success('cURL已复制')
  })
}
</script>
<template>
  <div>
    <el-dialog v-model="json_show" title="数据查看" width="800px"
               :close-on-click-modal="false"
               :close-on-press-escape="false"
               :show-close="false">
      <div>
        <el-scrollbar height="300px">
          <VueJsonPretty :data="json_str"></VueJsonPretty>
        </el-scrollbar>
      </div>
      <template #footer>
        <div class="dialog-footer">
          <el-button @click="json_show = false">关闭</el-button>
          <el-button @click="copyClick()" type="primary">复制</el-button>
        </div>
      </template>
    </el-dialog>

    <el-card>
      <template #header>请求日志</template>
      <div>
        <div class="input_line_wrapper">
          <div my-1 class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">搜索</el-tag>
          </div>
          <div ml-2 my-1>
            <el-input @keydown.enter="searchClick()" class="input_line_input_wrapper" v-model="page_options.search"
                      placeholder="请输入搜索"></el-input>
          </div>
          <div ml-2 my-1 class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">时间范围</el-tag>
          </div>
          <div ml-2 my-1>
            <el-date-picker v-model="page_options.time" type="daterange" range-separator="至"
                            start-placeholder="开始时间" end-placeholder="结束时间" value-format="YYYY-MM-DD"/>
          </div>
          <div ml-2 my-1 class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">类型</el-tag>
          </div>
          <div ml-2 my-1>
            <el-select v-model="page_options.method" class="input_line_input_wrapper"
                       placeholder="请选择类型">
              <el-option label="全部" value=""/>
              <el-option label="POST" value="POST"/>
              <el-option label="GET" value="GET"/>
            </el-select>
          </div>
          <div ml-2 my-1 class="input_line_tag_wrapper">
            <el-tag disable-transitions w-full type="info">返回码</el-tag>
          </div>
          <div ml-2 my-1>
            <el-input @keydown.enter="searchClick()" class="input_line_input_wrapper" v-model="page_options.code"
                      placeholder="请输入返回码"></el-input>
          </div>
          <el-button my-1 @click="searchClick()" ml-3 type="primary">搜索</el-button>
          <el-button my-1 @click="searchClearClick()" type="warning">清空</el-button>
        </div>
        <el-table mt-1 border :data="table_list" style="width: 100%">
          <el-table-column label="身份信息" width="500">
            <template #default="scope">
              <div my-1 class="input_line_wrapper">
                <div class="input_line_tag_wrapper">
                  <el-tag disable-transitions w-full type="info">UUID</el-tag>
                </div>
                <div ml-2>{{ scope.row.uuid }}</div>
              </div>
              <div my-1 class="input_line_wrapper">
                <div class="input_line_tag_wrapper">
                  <el-tag disable-transitions w-full type="info">Token</el-tag>
                </div>
                <div ml-2>{{ scope.row.token }}</div>
              </div>
              <div my-1 class="input_line_wrapper">
                <div class="input_line_tag_wrapper">
                  <el-tag disable-transitions w-full type="info">IP</el-tag>
                </div>
                <div ml-2>{{ scope.row.ip }}</div>
              </div>
            </template>
          </el-table-column>
          <el-table-column label="请求信息" width="400">
            <template #default="scope">
              <div my-1 class="input_line_wrapper">
                <div class="input_line_tag_wrapper">
                  <el-tag disable-transitions w-full type="info">Url</el-tag>
                </div>
                <div ml-2>{{ scope.row.url }}</div>
              </div>
              <div my-1 class="input_line_wrapper">
                <div class="input_line_tag_wrapper">
                  <el-tag disable-transitions w-full type="info">类型</el-tag>
                </div>
                <div ml-2>
                  <el-tag disable-transitions v-if="scope.row.method === 'GET'" type="success">GET</el-tag>
                  <el-tag disable-transitions v-else-if="scope.row.method === 'POST'">POST</el-tag>
                  <el-tag disable-transitions v-else type="warning">{{ scope.row.method }}</el-tag>
                </div>
              </div>
              <div my-1 class="input_line_wrapper">
                <div class="input_line_tag_wrapper">
                  <el-tag disable-transitions w-full type="info">参数</el-tag>
                </div>
                <div ml-2>
                  <el-button v-if="scope.row.type === 1" :disabled="!scope.row.result"
                             @click="jsonShowClick({
                  params:JSON.parse(scope.row.params),
                  input:JSON.parse(scope.row.input),
                  header:JSON.parse(scope.row.header),
                  })" type="primary" size="small">查看
                  </el-button>
                  <el-button v-else @click="txtShowClick({
                  id:scope.row.id,
                  created_at:scope.row.created_at,
                  type:'input'
                  })"
                             type="primary" size="small">查看
                  </el-button>
                  <el-button v-if="scope.row.type === 1" :disabled="!scope.row.result"
                             @click="cUrlCopyClick({
                  url:scope.row.url,
                  method:scope.row.method,
                  params:JSON.parse(scope.row.params),
                  input:JSON.parse(scope.row.input),
                  header:JSON.parse(scope.row.header),
                  result:JSON.parse(scope.row.result),
                  })" type="success" size="small">复制cURL
                  </el-button>
                  <el-button v-else @click="cUrlJsonCopyClick({
                  id:scope.row.id,
                  created_at:scope.row.created_at,
                  type:'curl'
                  })"
                             type="success" size="small">复制cURL
                  </el-button>
                </div>
              </div>
            </template>
          </el-table-column>
          <el-table-column label="处理信息" width="250">
            <template #default="scope">
              <div my-1 class="input_line_wrapper">
                <div class="input_line_tag_wrapper">
                  <el-tag disable-transitions w-full type="info">存储类型</el-tag>
                </div>
                <div ml-2>
                  <el-tag disable-transitions :type="scope.row.type === 1 ? '' : 'success'">{{
                      scope.row.type === 1 ? '数据库' : '文件'
                    }}
                  </el-tag>
                </div>
              </div>
              <div my-1 class="input_line_wrapper">
                <div class="input_line_tag_wrapper">
                  <el-tag disable-transitions w-full type="info">返回码</el-tag>
                </div>
                <div ml-2>
                  <el-tag disable-transitions :type="Number(scope.row.code) === 200 ? 'success' : 'warning'">
                    {{ !!scope.row.code ? scope.row.code : '未处理完成' }}
                  </el-tag>
                </div>
              </div>
              <div my-1 class="input_line_wrapper">
                <div class="input_line_tag_wrapper">
                  <el-tag disable-transitions w-full type="info">返回</el-tag>
                </div>
                <div ml-2>
                  <el-button v-if="scope.row.type === 1" :disabled="!scope.row.result"
                             @click="jsonShowClick(JSON.parse(scope.row.result))"
                             type="primary" size="small">查看
                  </el-button>
                  <el-button v-else :disabled="!scope.row.result" @click="txtShowClick({
                  id:scope.row.id,
                  created_at:scope.row.created_at,
                  type:'result'
                  })"
                             type="primary" size="small">查看
                  </el-button>
                </div>
              </div>
            </template>
          </el-table-column>
          <el-table-column label="处理时间">
            <template #default="scope">
              <div my-1 class="input_line_wrapper">
                <div class="input_line_tag_wrapper">
                  <el-tag disable-transitions w-full type="info">请求时间</el-tag>
                </div>
                <div ml-2>{{ scope.row.created_at }}</div>
              </div>
              <div my-1 class="input_line_wrapper">
                <div class="input_line_tag_wrapper">
                  <el-tag disable-transitions w-full type="info">返回时间</el-tag>
                </div>
                <div ml-2>{{ scope.row.updated_at }}</div>
              </div>
              <div my-1 class="input_line_wrapper">
                <div class="input_line_tag_wrapper">
                  <el-tag disable-transitions w-full type="info">执行速度</el-tag>
                </div>
                <div ml-2>{{ !!scope.row.spend ? `${scope.row.spend}` : '0' }}s</div>
              </div>
            </template>
          </el-table-column>
        </el-table>
        <el-pagination v-if="last_page > 1" :current-page="page_options.page" mt-2 background layout="prev, pager, next"
                       :page-count="last_page" @update:current-page="searchClick"/>
      </div>
    </el-card>
  </div>
</template>
<style>

</style>
<style scoped>

</style>
<route>
{"meta":{"title":"请求日志"}}
</route>
