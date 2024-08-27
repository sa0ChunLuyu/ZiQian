import path from 'node:path'
import {defineConfig} from 'vite'
import Vue from '@vitejs/plugin-vue'
import Layouts from 'vite-plugin-vue-layouts'
import Components from 'unplugin-vue-components/vite'
import AutoImport from 'unplugin-auto-import/vite'
import VueMacros from 'unplugin-vue-macros/vite'
import VueDevTools from 'vite-plugin-vue-devtools'
import Unocss from 'unocss/vite'
import VueRouter from 'unplugin-vue-router/vite'
import {VueRouterAutoImports} from 'unplugin-vue-router'
import {ElementPlusResolver} from 'unplugin-vue-components/resolvers'
import {presetAttributify, presetIcons, presetUno} from "unocss";
import {viteCommonjs} from '@originjs/vite-plugin-commonjs'

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
    VueMacros({
      plugins: {
        vue: Vue({
          include: [/\.vue$/],
        }),
      },
    }),
    VueRouter({
      extensions: ['.vue'],
    }),
    Layouts(),
    AutoImport({
      resolvers: [ElementPlusResolver({ importStyle: 'sass' })],
      imports: [
        'vue',
        // 'vue/macros',
        'vue-router',
        '@vueuse/core',
        VueRouterAutoImports
      ],
      vueTemplate: true,
    }),
    Components({
      extensions: ['vue'],
      include: [/\.vue$/, /\.vue\?vue/],
      resolvers: [ElementPlusResolver({ importStyle: 'sass' })]
    }),
    Unocss({
      presets: [
        presetAttributify({}),
        presetUno(),
        presetIcons({
          warn: true,
        })
      ],
    }),
    VueDevTools(),
    viteCommonjs()
  ]
})
