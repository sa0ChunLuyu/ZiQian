import {defineStore} from 'pinia'

export const useStore = defineStore('main', {
  state: () => {
    return {
      api: false,
      info: null,
      loading: 0
    }
  }
})
const TOKEN_KEY = JSON.parse(localStorage.getItem('APP_CONFIG') ?? '{}').token
export const useConfig = createGlobalState(() => useStorage('APP_CONFIG', JSON.parse(localStorage.getItem('APP_CONFIG') ?? '{}')))
export const useToken = createGlobalState(() => useStorage(TOKEN_KEY, ''))
export const useSessionToken = createGlobalState(() => useStorage(TOKEN_KEY, '', sessionStorage))
export const useSaveTokenType = createGlobalState(() => useStorage('SAVE_TOKEN_TYPE', 'session'))
export const useLoginType = createGlobalState(() => useStorage('LOGIN_TYPE', 'login'))
export const useCollapsed = createGlobalState(() => useStorage('COLLAPSED', false))
export const useRouterActive = createGlobalState(() => useStorage('ROUTER_ACTIVE', []))
export const useIpNotification = createGlobalState(() => useStorage('IP_NOTIFICATION', false))
export const usePageSpy = createGlobalState(() => useStorage('PageSpy', []))