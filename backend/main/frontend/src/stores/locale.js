import { defineStore } from 'pinia'

const STORAGE_KEY = 'spa_locale'

export const useLocaleStore = defineStore('locale', {
  state: () => ({
    lang: typeof localStorage !== 'undefined' ? localStorage.getItem(STORAGE_KEY) || 'lv' : 'lv',
  }),
  actions: {
    setLang(next) {
      if (next !== 'lv' && next !== 'en') return
      this.lang = next
      try {
        localStorage.setItem(STORAGE_KEY, next)
      } catch {
        /* ignore */
      }
      if (typeof document !== 'undefined') {
        document.documentElement.lang = next === 'en' ? 'en' : 'lv'
      }
    },
  },
})
