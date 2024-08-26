<script setup>
/**
 * name：
 * user：sa0ChunLuyu
 * date：2023年7月27日 10:46:03
 */
import {useStore} from "~/store";

const $store = useStore()

const timeTip = () => {
  let currentTime = new Date();
  let currentHour = currentTime.getHours();
  let tip = ''
  if (currentHour >= 0 && currentHour < 6) {
    tip = "夜深了，注意休息！"
  } else if (currentHour >= 6 && currentHour < 12) {
    tip = "早上好！新的一天开始了，加油！"
  } else if (currentHour >= 12 && currentHour < 14) {
    tip = "中午好！休息一下，放松一下眼睛。"
  } else if (currentHour >= 14 && currentHour < 18) {
    tip = "下午好！继续加油，离目标越来越近了！"
  } else {
    tip = "晚上好！做个深呼吸，放松一下压力。"
  }
  return tip
}
</script>
<template>
  <div>
    <div class="index_page_wrapper">
      <div class="index_tip_wrapper">
        <div class="index_icon_wrapper">
          <Icon type="waves-right" :size="30"></Icon>
        </div>
        <div class="index_content_wrapper" ml-5>
          <div class="index_title_wrapper">{{ timeTip() }}</div>
          <div class="index_desc_wrapper" mt-3>欢迎使用{{ $store.config['网站名称'] }}后台管理系统</div>
          <div class="index_desc_wrapper" mt-3>点击左侧导航栏开始操作</div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.index_page_wrapper {
  min-height: calc(100vh - 55px - 40px);
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background: #ffffff;

  .index_tip_wrapper {
    display: flex;
    align-items: center;
    width: 600px;
    background: #eaf4fe;
    color: #3095fa;
    padding: 30px;
    position: absolute;
    top: 30%;
    left: 50%;
    transform: translate(-50%, -50%);

    .index_title_wrapper {
      font-size: 20px;
    }
  }
}
</style>
<route>
{"meta":{"title":"首页"}}
</route>

