/// <reference types="vitest" />

import path from 'node:path'
import {defineConfig} from 'vite'
import Vue from '@vitejs/plugin-vue'
import Components from 'unplugin-vue-components/vite'
import AutoImport from 'unplugin-auto-import/vite'
import UnoCSS from 'unocss/vite'
import VueMacros from 'unplugin-vue-macros/vite'
import VueRouter from 'unplugin-vue-router/vite'
import {VueRouterAutoImports} from 'unplugin-vue-router'
import {ElementPlusResolver} from 'unplugin-vue-components/resolvers'

const package_path = 'mana'
export default defineConfig({
  base: `/${package_path}/`,
  build: {
    outDir: `../api/public/${package_path}`,
    assetsDir: 'lib'
  },
  resolve: {
    alias: {
      '~/': `${path.resolve(__dirname, 'src')}/`,
    },
  },
  plugins: [
    VueMacros({
      defineOptions: false,
      defineModels: false,
      plugins: {
        vue: Vue({
          script: {
            propsDestructure: true,
            defineModel: true,
          },
        }),
      },
    }),
    VueRouter(),
    AutoImport({
      resolvers: [ElementPlusResolver()],
      imports: [
        'vue',
        '@vueuse/core',
        VueRouterAutoImports,
        {
          'vue-router/auto': ['useLink'],
        },
      ],
      dts: true,
      vueTemplate: true,
    }),
    Components({
      resolvers: [ElementPlusResolver()],
      dts: true,
    }),
    UnoCSS(),
  ],
  test: {
    environment: 'jsdom',
  },
})
