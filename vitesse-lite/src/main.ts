import { createApp } from 'vue'
import { createRouter, createWebHashHistory } from 'vue-router'
import { routes } from 'vue-router/auto-routes'
import App from './App.vue'
import {createPinia} from 'pinia'

import '@unocss/reset/tailwind.css'
import './styles/main.css'
import 'uno.css'
import 'normalize.css'
import '@icon-park/vue-next/styles/index.css'
import 'element-plus/dist/index.css'

const app = createApp(App)
const router = createRouter({
  routes,
  history: createWebHashHistory(import.meta.env.BASE_URL),
})
app.use(router)
app.use(createPinia())
app.mount('#app')
