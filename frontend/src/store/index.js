// src/store/index.js
import { createStore } from 'vuex'
import axios from 'axios'       // こちらは「素のaxios」（baseURLなし）
import instance from '../axios' // baseURL=http://localhost:8000/api のインスタンス

export default createStore({
  state: {
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || '',
    registeredWorks: [], // 追加
  },
  mutations: {
    SET_USER(state, user) {
      state.user = user;
      localStorage.setItem('user', JSON.stringify(user));
      // ユーザー情報に登録済み作品が含まれている場合、registeredWorksを設定
      state.registeredWorks = user.registeredWorks || [];
    },
    SET_TOKEN(state, token) {
      state.token = token;
      localStorage.setItem('token', token);
    },
    LOGOUT(state) {
      state.user = null;
      state.token = '';
      state.registeredWorks = []; // 登録済み作品をクリア
      localStorage.removeItem('user');
      localStorage.removeItem('token');
    },
    REGISTER_WORK(state, work) { // 修正: workオブジェクトを受け取る
      const exists = state.registeredWorks.some(
        (registeredWork) =>
          registeredWork.media_id === work.media_id && registeredWork.media_type === work.media_type
      )
      if (!exists) {
        state.registeredWorks.push(work);
      }
    },
    SET_REGISTERED_WORKS(state, works) { // 追加
      state.registeredWorks = works;
    },
  },
  actions: {
    // ログインアクション
    async login({ commit, dispatch }, credentials) {
      try {
        // (A) CSRF CookieはフルURLで素のaxiosを使う（baseURLなし）
        await axios.get(process.env.VUE_APP_SANCTUM_CSRF_COOKIE, { withCredentials: true });

        // (B) 実際のログインは baseURL=/api のinstanceでPOST
        const { data } = await instance.post('/login', credentials);

        // (C) ステート更新
        commit('SET_TOKEN', data.access_token);
        commit('SET_USER', data.user);
        await dispatch('loadRegisteredWorks'); // 追加
      } catch (error) {
        console.error('ログインに失敗しました。', error);
        throw error;
      }
    },

    // ログアウト
    async logout({ commit }) {
      try {
        await instance.post('/logout'); // baseURL=/api のまま
      } catch (error) {
        console.error('ログアウトに失敗しました。', error);
      }
      commit('LOGOUT');
    },

    // ユーザー登録
    async register({ commit, dispatch }, userData) {
      try {
        // (A) CSRF CookieはフルURLで素のaxiosを使う（baseURLなし）
        await axios.get(process.env.VUE_APP_SANCTUM_CSRF_COOKIE, { withCredentials: true });

        // (B) 実際の登録 
        const { data } = await instance.post('/register', userData);
        commit('SET_TOKEN', data.access_token);
        commit('SET_USER', data.user);
        await dispatch('loadRegisteredWorks'); // 追加
      } catch (error) {
        console.error('ユーザー登録に失敗しました。', error);
        throw error;
      }
    },

    // 認証済みユーザー確認
    async checkAuth({ commit, dispatch }) {
      try {
        // (A) CSRF CookieはフルURLで 
        await axios.get(process.env.VUE_APP_SANCTUM_CSRF_COOKIE, { withCredentials: true });

        // (B) 認証済みユーザー情報を取得 
        const { data } = await instance.get('/user');
        commit('SET_USER', data);
        await dispatch('loadRegisteredWorks'); // 追加
      } catch (error) {
        console.warn('checkAuthでエラー: ', error);
        commit('LOGOUT');
      }
    },

    // 登録済み作品の取得
    async loadRegisteredWorks({ commit }) {
      try {
        const { data } = await instance.get('/user/registered-works');
        commit('SET_REGISTERED_WORKS', data);
      } catch (error) {
        console.error('登録済み作品の取得に失敗しました。', error);
      }
    },

    // 作品を登録するアクション
    async registerWork({ dispatch }, work) {
      try {
        await instance.post(`/media/${work.media_type}/${work.tmdb_id}/register`);
       // 登録後に改めて取得
        await dispatch('loadRegisteredWorks');
      } catch (error) {
        console.error('作品の登録に失敗しました。', error);
        throw error;
      }
    },
  },
  getters: {
    isAuthenticated: (state) => !!state.token,
    getUser: (state) => state.user,
    isAdmin: (state) => state.user && state.user.is_admin,
    isRegistered: (state) => (media_type, workId) => {
      console.log('--- isRegistered Check ---');
      console.log('Media Type:', media_type);
      console.log('Work ID:', workId);
      state.registeredWorks.forEach((registeredWork) => {
        console.log(`Comparing with Registered Work - media_type: ${registeredWork.media_type}, media_id: ${registeredWork.media_id}`);
        console.log(`Types - workId: ${typeof workId}, registeredWork.media_id: ${typeof registeredWork.media_id}`);
      });
      const isReg = state.registeredWorks.some(
        (registeredWork) =>
          registeredWork.media_id === workId && registeredWork.media_type === media_type
      );
      console.log('Is Registered:', isReg);
      return isReg;
    }
    
  },
});
