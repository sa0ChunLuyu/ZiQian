<script setup>
/**
 * name：
 * user：sa0ChunLuyu
 * date：2023年6月5日 14:04:22
 */
import {
  $api,
  $image,
  $response
} from '~/api'
import {onMounted} from "vue";

const $props = defineProps({
  content: {
    type: String,
    default: ''
  },
  width: {
    type: Number,
    default: 1000
  }
})

onBeforeUnmount(() => {
  tinymce.remove()
})

onMounted(() => {
  createTinymce()
})

const createTinymce = () => {
  tinymce.init({
    selector: `#editor`,
    language: 'zh_CN',
    plugins: "code image axupimgs",
    toolbar: 'undo redo ' +
        '| code axupimgs' +
        '| formatselect fontselect fontsizeselect ' +
        '| bold italic underline strikethrough ' +
        '| alignleft aligncenter alignright alignjustify ' +
        '| cut copy paste ' +
        '| bullist numlist ' +
        '| outdent indent ' +
        '| blockquote removeformat ' +
        '| subscript superscript',
    menubar: false,
    width: $props.width,
    height: 300,
    branding: false,
    images_upload_handler: function (blobInfo, succFun) {
      let file = blobInfo.blob();
      if (window.FileReader) {
        let reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onloadend = async (e) => {
          const base64 = e.target.result;
          const response = await $api('AdminUploadImage', {
            base64
          })
          $response(response, () => {
            succFun($image(response.data.url));
          })
        };
      }
    }
  });
  tinymce.activeEditor.setContent($props.content)
}

const getContent = () => {
  return tinymce.activeEditor.getContent()
}
const getText = () => {
  let activeEditor = tinymce.activeEditor;
  let editBody = activeEditor.getBody();
  activeEditor.selection.select(editBody);
  let text = activeEditor.selection.getContent({format: 'text'})
  return text.split('\n').join('').replace(/^\s+|\s+$/g, "")
}

defineExpose({
  getContent,
  getText
})
</script>
<template>
  <div>
    <div class="editor_container_wrapper">
      <textarea id="editor">{{ $props.content }}</textarea>
    </div>
  </div>
</template>
<style>
.tox-tinymce-aux {
  z-index: 99999999 !important;
}
</style>
<style scoped>
#editor {
  width: 100%;
  height: 100%;
}

.editor_container_wrapper {
  width: 800px;
  min-height: 300px;
  position: relative;
}
</style>

