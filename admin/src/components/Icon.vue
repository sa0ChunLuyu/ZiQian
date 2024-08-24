<script setup>
/**
 * name：
 * user：sa0ChunLuyu
 * date：2022年12月25日 19:57:16
 */
import {IconPark} from '@icon-park/vue-next/es/all';

const $props = defineProps({
  type: {
    type: String,
    default: 'AddText'
  },
  size: {
    type: Number,
    default: 20
  },
  theme: {
    type: String,
    default: 'outline'
  }
})
</script>
<template>
  <IconPark :type="$props.type" :size="$props.size" :theme="$props.theme"></IconPark>
</template>

