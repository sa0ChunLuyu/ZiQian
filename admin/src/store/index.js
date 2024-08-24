import {defineStore} from 'pinia'

export const useStore = defineStore('main', {
  state: () => {
    return {
      api_map: {},
      info: null,
      loading: 0
    }
  }
})
const TOKEN_KEY = JSON.parse(localStorage.getItem('APP_CONFIG') ?? '{}').token_key
export const useConfig = createGlobalState(() => useStorage('APP_CONFIG', JSON.parse(localStorage.getItem('APP_CONFIG') ?? '{}')))

export const useToken = createGlobalState(() => useStorage(TOKEN_KEY, ''))
export const useSessionToken = createGlobalState(() => useStorage(TOKEN_KEY, '', sessionStorage))
export const useSaveTokenType = createGlobalState(() => useStorage('SAVE_TOKEN_TYPE', 'session'))
export const useRouterActive = createGlobalState(() => useStorage('ROUTER_ACTIVE', []))
export const useCollapsed = createGlobalState(() => useStorage('COLLAPSED', false))
export const useProxyShow = createGlobalState(() => useStorage('PROXY_SHOW', false))
export const useIpNotification = createGlobalState(() => useStorage('IP_NOTIFICATION', false))