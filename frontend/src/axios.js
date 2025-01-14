import axios from 'axios';
import store from './store';

console.log('VUE_APP_API_BASE_URL:', process.env.VUE_APP_API_BASE_URL);

const instance = axios.create({
  baseURL: process.env.VUE_APP_API_BASE_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
});

// リクエストインターセプター
instance.interceptors.request.use(
  config => {
    const token = store.state.token;
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  error => Promise.reject(error)
);

export default instance;
