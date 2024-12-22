// store/index.js
import { createStore } from 'vuex'
import axios from 'axios'       // こちらは「素のaxios」（baseURLなし）
import instance from '../axios' // baseURL=http://localhost:8000/api のインスタンス

export default createStore({
  state: {
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || '',
  },
  mutations: {
    SET_USER(state, user) {
      state.user = user
      localStorage.setItem('user', JSON.stringify(user))
    },
    SET_TOKEN(state, token) {
      state.token = token
      localStorage.setItem('token', token)
    },
    LOGOUT(state) {
      state.user = null
      state.token = ''
      localStorage.removeItem('user')
      localStorage.removeItem('token')
    },
  },
  actions: {
    // ログインアクション
    async login({ commit }, credentials) {
      try {
        // (A) CSRF CookieはフルURLで素のaxiosを使う（baseURLなし）
        await axios.get('http://localhost:8000/sanctum/csrf-cookie', { withCredentials: true })

        // (B) 実際のログインは baseURL=/api のinstanceでPOST
        const { data } = await instance.post('/login', credentials)

        // (C) ステート更新
        commit('SET_TOKEN', data.access_token)
        commit('SET_USER', data.user)
      } catch (error) {
        console.error('ログインに失敗しました。', error)
        throw error
      }
    },

    // ログアウト
    async logout({ commit }) {
      try {
        await instance.post('/logout') // baseURL=/api のまま
      } catch (error) {
        console.error('ログアウトに失敗しました。', error)
      }
      commit('LOGOUT')
    },

    // ユーザー登録
    async register({ commit }, userData) {
      try {
        // (A) CSRF CookieだけフルURL
        await axios.get('http://localhost:8000/sanctum/csrf-cookie', { withCredentials: true })

        // (B) 実際の登録
        const { data } = await instance.post('/register', userData)
        commit('SET_TOKEN', data.access_token)
        commit('SET_USER', data.user)

      } catch (error) {
        console.error('ユーザー登録に失敗しました。', error)
        throw error
      }
    },

    // 認証済みユーザー確認
    async checkAuth({ commit }) {
      try {
        // (A) CSRF CookieはフルURLで
        await axios.get('http://localhost:8000/sanctum/csrf-cookie', { withCredentials: true })

        // (B) 認証済みユーザー情報を取得
        const { data } = await instance.get('/user')
        commit('SET_USER', data)

      } catch (error) {
        console.warn('checkAuthでエラー: ', error)
        commit('LOGOUT')
      }
    },
  },
  getters: {
    isAuthenticated: (state) => !!state.token,
    getUser: (state) => state.user,
    isAdmin: (state) => state.user && state.user.is_admin,
  },
})
