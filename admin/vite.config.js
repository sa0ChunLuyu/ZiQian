import {defineConfig} from 'vite'
import Vue from '@vitejs/plugin-vue'
import path from 'path'
import Pages from 'vite-plugin-pages'
import Layouts from 'vite-plugin-vue-layouts'
import Components from 'unplugin-vue-components/vite'
import AutoImport from 'unplugin-auto-import/vite'
import {ElementPlusResolver} from 'unplugin-vue-components/resolvers'
import {viteCommonjs} from '@originjs/vite-plugin-commonjs'
import UnoCSS from 'unocss/vite'
import {presetAttributify, presetUno, presetIcons} from 'unocss'

const package_path = 'mana'
export default defineConfig({
  base: `/${package_path}/`,
  build: {
    outDir: `../laravel/public/${package_path}`,
    assetsDir: 'lib'
  },
  resolve: {
    alias: {
      '~/': `${path.resolve(__dirname, 'src')}/`
    }
  },
  plugins: [
    Vue({
      reactivityTransform: true
    }),
    viteCommonjs(),
    Pages(),
    Layouts(),
    AutoImport({
      resolvers: [ElementPlusResolver()],
      imports: [
        'vue',
        'vue/macros',
        'vue-router',
        '@vueuse/core'
      ],
    }),
    Components({
      resolvers: [ElementPlusResolver()]
    }),
    UnoCSS({
      presets: [
        presetAttributify({}),
        presetUno(),
        presetIcons({
          warn: true,
        })],
    })]
})
