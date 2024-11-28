<template>
  <div id="app">
    <header class="header">
      <div class="logo">
        <router-link to="/">映画スケジューラー</router-link>
      </div>
      <nav class="nav">
        <button class="hamburger" @click="toggleMenu" aria-label="メニューを開閉">
          <span class="hamburger-line"></span>
          <span class="hamburger-line"></span>
          <span class="hamburger-line"></span>
        </button>
        <ul :class="{'nav-menu': true, 'is-active': isMenuActive}">
          <li><router-link to="/">ホーム</router-link></li>
          <li v-if="isAuthenticated"><router-link to="/mypage">マイページ</router-link></li>
          <li><router-link to="/search">作品検索</router-link></li>
          <li v-if="isAuthenticated"><router-link to="/schedule">スケジュール管理</router-link></li>
          <!-- <li v-if="isAuthenticated"><router-link to="/reminder">リマインダー設定</router-link></li> -->
          <li><router-link to="/reviews">レビューランキング</router-link></li>
          <li v-if="isAuthenticated"><router-link to="/settings">設定</router-link></li>
          <li v-if="isAuthenticated"><a href="#" @click.prevent="confirmLogout">ログアウト</a></li>
        </ul>
      </nav>
    </header>

    <main>
      <h1 class="page-title">{{ pageTitle }}</h1>
      <router-view v-slot="{ Component }">
        <keep-alive include="WorkSearch">
          <component :is="Component" />
        </keep-alive>
      </router-view>
    </main>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useStore } from 'vuex'
import { useRoute, useRouter } from 'vue-router'

export default {
  name: 'App',
  setup() {
    const store = useStore()
    const route = useRoute()
    const router = useRouter()
    const isAuthenticated = computed(() => store.getters.isAuthenticated)
    const isMenuActive = ref(false)

    const toggleMenu = () => {
      isMenuActive.value = !isMenuActive.value
    }

    const logout = () => {
      store.dispatch('logout')
      // ログアウト後にホームページにリダイレクト
      router.push('/')
    }

    const confirmLogout = () => {
      if (window.confirm('ログアウトしますか？')) {
        logout()
      }
    }

    const pageTitle = computed(() => {
      return route.meta.title || ''
    })

    return {
      isAuthenticated,
      isMenuActive,
      toggleMenu,
      confirmLogout,
      pageTitle,
    }
  },
}
</script>

<style scoped>
/* リセットと基本スタイル */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

#app {
  font-family: 'Helvetica Neue', Arial, sans-serif;
  color: #333;
}

a {
  text-decoration: none;
  color: inherit;
}

/* ヘッダーのスタイル */
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: #ff7e5f;
  padding: 0.5em 1em;
}

.logo a {
  font-size: 1.5em;
  font-weight: bold;
  color: #fff;
}

.nav {
  position: relative;
}

.hamburger {
  display: none;
  flex-direction: column;
  justify-content: space-around;
  width: 30px;
  height: 24px;
  background: transparent;
  border: none;
  cursor: pointer;
}

.hamburger-line {
  width: 100%;
  height: 3px;
  background-color: #fff;
}

.nav-menu {
  display: flex;
  list-style: none;
}

.nav-menu li {
  margin-left: 1em;
}

.nav-menu li a {
  color: #fff;
  transition: color 0.3s;
}

.nav-menu li a:hover {
  color: #ffe0d1;
}

/* 現在のページタイトル */
.page-title {
  font-size: 2em;
  margin: 1em;
  text-align: center;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
  .hamburger {
    display: flex;
  }

  .nav-menu {
    flex-direction: column;
    position: absolute;
    top: 60px;
    right: 0;
    background-color: #ff7e5f;
    width: 200px;
    transform: translateX(100%);
    transition: transform 0.3s ease-in-out;
  }

  .nav-menu.is-active {
    transform: translateX(0);
  }

  .nav-menu li {
    margin: 0;
    padding: 1em;
  }
}
</style>
