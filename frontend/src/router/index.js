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
    meta: { title: 'マイページ' },
  },
  {
    path: '/search',
    name: 'WorkSearch',
    component: WorkSearch,
    meta: { title: '作品検索' },
  },
  {
    path: '/work/:media_type/:id',
    name: 'WorkDetail',
    component: WorkDetail,
    props: true,
    meta: { title: '作品詳細' },
  },
  {
    path: '/schedule',
    name: 'ScheduleManagement',
    component: ScheduleManagement,
    meta: { title: 'スケジュール管理' },
  },
  {
    path: '/reminder',
    name: 'ReminderSettings',
    component: ReminderSettings,
    meta: { title: 'リマインダー設定' },
  },
  // レビューの新規投稿用ルート
  {
    path: '/review/post',
    name: 'ReviewPostSelect',
    component: ReviewPost,
    meta: { title: 'レビュー投稿' },
  },
  {
    path: '/review/:media_type/:id',
    name: 'ReviewPost',
    component: ReviewPost,
    props: true,
    meta: { title: 'レビュー投稿' },
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
    meta: { title: 'レビュー一覧' },
  },
  {
    path: '/settings',
    name: 'UserSettings',
    component: UserSettings,
    meta: { title: '設定' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
