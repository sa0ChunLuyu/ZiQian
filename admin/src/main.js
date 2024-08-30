import {createApp} from 'vue'
import App from './App.vue'
import router from '~/router'
import {createPinia} from 'pinia'
import './styles/main.css'
import 'uno.css'
import 'normalize.css'
import '@icon-park/vue-next/styles/index.css'
import 'element-plus/dist/index.css'
import './styles/element.css'
import 'vue-json-pretty/lib/styles.css'

const app = createApp(App)
app.use(router)
app.use(createPinia())
app.mount('#app')
