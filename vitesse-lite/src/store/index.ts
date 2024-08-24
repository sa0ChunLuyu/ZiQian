import {defineStore} from 'pinia'

export const useStore = defineStore('main', {
  state: () => {
    return {
      api: {},
      loading: 0
    }
  }
})
const TOKEN_KEY = JSON.parse(localStorage.getItem('APP_CONFIG') ?? '{}').token_key
export const useConfig = createGlobalState(() => useStorage('APP_CONFIG', JSON.parse(localStorage.getItem('APP_CONFIG') ?? '{}')))

export const useToken = createGlobalState(() => useStorage(TOKEN_KEY, ''))
