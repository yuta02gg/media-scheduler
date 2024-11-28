import { createStore } from 'vuex'
import axios from '../axios' // Axios インスタンスを作成します

export default createStore({
  state: {
    user: null,
    token: localStorage.getItem('token') || '',
  },
  mutations: {
    SET_USER(state, user) {
      state.user = user
    },
    SET_TOKEN(state, token) {
      state.token = token
      localStorage.setItem('token', token)
    },
    LOGOUT(state) {
      state.user = null
      state.token = ''
      localStorage.removeItem('token')
    },
  },
  actions: {
    async login({ commit }, credentials) {
      const response = await axios.post('/login', credentials)
      commit('SET_TOKEN', response.data.access_token)
      const userResponse = await axios.get('/user')
      commit('SET_USER', userResponse.data)
    },
    async logout({ commit }) {
      // サーバー側でログアウトが必要な場合は、以下のコメントを外してください
      // await axios.post('/logout')

      commit('LOGOUT')
    },
    async register({ commit }, userData) {
      const response = await axios.post('/register', userData)
      commit('SET_TOKEN', response.data.access_token)
      commit('SET_USER', response.data.user)
    },
    
    // 他のアクションを追加
  },
  getters: {
    isAuthenticated: (state) => !!state.token,
    getUser: (state) => state.user,
  },
})
