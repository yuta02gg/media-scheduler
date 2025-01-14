import { createApp } from 'vue'
import App from './App.vue'
import store from './store'
import router from './router'

const app = createApp(App)

app.use(store)
app.use(router)

app.mount('#app')

// アプリ起動時に認証確認
store.dispatch('checkAuth').catch(error => {
  console.error('認証状態の確認に失敗しました:', error)
})
