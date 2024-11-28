import axios from 'axios'
import store from './store'

const instance = axios.create({
  baseURL: 'http://localhost:8000/api', // API のベース URL を指定
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true, // Cookieを含める設定
})

// リクエスト時にトークンをヘッダーに追加
instance.interceptors.request.use(config => {
  const token = store.state.token
  if (token) {
    config.headers['Authorization'] = `Bearer ${token}`
  }
  return config
}, error => {
  return Promise.reject(error)
})

export default instance
