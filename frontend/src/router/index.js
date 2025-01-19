// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import HomePage from '../views/Home.vue'
import UserLogin from '../views/Login.vue'
import UserRegister from '../views/Register.vue'
import MyPageComponent from '../views/MyPage.vue'
import WorkSearch from '../views/WorkSearch.vue'
import WorkDetail from '../views/WorkDetail.vue'
import ScheduleManagement from '../views/Schedule.vue'
import ReminderSettings from '../views/Reminder.vue'
import ReviewPost from '../views/ReviewPost.vue'
import ReviewList from '../views/ReviewList.vue'
import ReviewRanking from '../views/ReviewRanking.vue'
import UserSettings from '../views/Settings.vue'

// 管理者関連のコンポーネントは動的インポートで遅延読み込み
const AdminDashboard = () => import('../views/AdminDashboard.vue')
const AdminUserManagement = () => import('../views/AdminUserManagement.vue')
const AdminReviewManagement = () => import('../views/AdminReviewManagement.vue')

import store from '../store' // Vuex ストアをインポート

const routes = [
  {
    path: '/',
    name: 'Home',
    component: HomePage,
    meta: { title: 'ホーム' },
  },
  {
    path: '/login',
    name: 'Login',
    component: UserLogin,
    meta: { title: 'ログイン' },
  },
  {
    path: '/register',
    name: 'Register',
    component: UserRegister,
    meta: { title: 'ユーザー登録' },
  },
  {
    path: '/mypage',
    name: 'MyPage',
    component: MyPageComponent,
    meta: { title: 'マイページ', requiresAuth: true },
  },
  {
    path: '/search',
    name: 'WorkSearch',
    component: WorkSearch,
    meta: { title: '作品検索' },
  },
  {
    path: '/media/:media_type/:tmdb_id', 
    name: 'WorkDetail',
    component: WorkDetail,
    props: true,
    meta: { title: '作品詳細' },
  },
  {
    path: '/schedule',
    name: 'ScheduleManagement',
    component: ScheduleManagement,
    meta: { title: 'スケジュール管理', requiresAuth: true },
  },
  {
    path: '/reminder',
    name: 'ReminderSettings',
    component: ReminderSettings,
    meta: { title: 'リマインダー設定', requiresAuth: true },
  },
  // レビューの新規投稿用ルート
  {
    path: '/review/post',
    name: 'ReviewPostSelect',
    component: ReviewPost,
    meta: { title: 'レビュー投稿', requiresAuth: true },
  },
  {
    path: '/review/:media_type/:id',
    name: 'ReviewPost',
    component: ReviewPost,
    props: true,
    meta: { title: 'レビュー投稿', requiresAuth: true },
  },
  // レビュー一覧表示用のルート
  {
    path: '/reviews',
    name: 'ReviewRanking',
    component: ReviewRanking,
    meta: { title: 'レビューランキング' },
  },
  {
    path: '/media/:media_type/:tmdb_id/reviews',
    name: 'ReviewList',
    component: ReviewList,
    props: true,
    meta: { title: 'レビュー一覧'},
  },
  {
    path: '/settings',
    name: 'UserSettings',
    component: UserSettings,
    meta: { title: '設定', requiresAuth: true },
  },
  // 管理者ダッシュボード
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: AdminDashboard,
    meta: { title: '管理者ダッシュボード', requiresAuth: true, isAdmin: true },
  },
  // 管理者ユーザー管理
  {
    path: '/admin/users',
    name: 'AdminUserManagement',
    component: AdminUserManagement,
    meta: { title: 'ユーザー管理', requiresAuth: true, isAdmin: true },
  },
  // 管理者レビュー管理
  {
    path: '/admin/reviews',
    name: 'AdminReviewManagement',
    component: AdminReviewManagement,
    meta: { title: 'レビュー管理', requiresAuth: true, isAdmin: true },
  },
  // ユーザーごとのレビュー管理
  {
    path: '/admin/users/:id/reviews', 
    name: 'AdminUserReviews',
    component: AdminReviewManagement,
    props: true,
    meta: { title: 'ユーザーのレビュー管理', requiresAuth: true, isAdmin: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// ナビゲーションガードの設定
router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const isAdminRoute = to.matched.some(record => record.meta.isAdmin)

  if (requiresAuth && !store.getters.isAuthenticated) {
    next({ name: 'Login' })
  } else if (requiresAuth && isAdminRoute && !store.getters.isAdmin) {
    next({ name: 'Home' }) // 適切なエラーページにリダイレクト
  } else {
    next()
  }
})

export default router
