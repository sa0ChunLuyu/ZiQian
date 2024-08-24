import {createRouter, createWebHashHistory} from 'vue-router'
import {setupLayouts} from 'virtual:generated-layouts'
import generatedRoutes from 'virtual:generated-pages'

const router = createRouter({
  history: createWebHashHistory(import.meta.env.BASE_URL), routes: setupLayouts(generatedRoutes)
})

router.beforeEach(async (to, from, next) => {
  next()
})
export default router
