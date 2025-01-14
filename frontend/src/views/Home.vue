<template>
  <div class="homepage-container">
    <header class="header">
      <h1 class="logo">映画スケジューラー</h1>
    </header>

    <div class="catchphrase">
      <h2>映画とドラマをもっと楽しもう！</h2>
      <p>スケジュール管理とレビュー共有で、あなたのエンターテインメントライフを充実させましょう。</p>
    </div>

    <div class="features">
      <div class="feature-box">
        <h3>簡単スケジューリング</h3>
        <p>映画やドラマのスケジュールを簡単に登録できます。</p>
      </div>
      <div class="feature-box">
        <h3>レビューを共有</h3>
        <p>視聴後の感想を投稿して、他のユーザーと共有しましょう。</p>
      </div>
    </div>

    <div class="cta-section">
      <router-link v-if="!isLoggedIn" to="/register" class="cta-button">
        新規登録
      </router-link>
      <router-link v-if="!isLoggedIn" to="/login" class="cta-button">
        ログイン
      </router-link>

      <router-link v-if="isLoggedIn" to="/mypage" class="cta-button">
        マイページ
      </router-link>
      <button v-if="isLoggedIn" @click="logout" class="cta-button">
        ログアウト
      </button>

      <router-link
        v-if="isAdmin"
        to="/admin/dashboard"
        class="cta-button admin-button"
      >
        管理者ダッシュボード
      </router-link>
    </div>
  </div>
</template>

<script>
import { computed, onMounted } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'HomePage',
  setup() {
    const store = useStore()
    const isLoggedIn = computed(() => store.getters.isAuthenticated)
    const isAdmin = computed(() => store.getters.isAdmin)

    const checkLoginStatus = async () => {
      try {
        await store.dispatch('checkAuth')
      } catch (error) {
        console.error('ログイン状態の確認に失敗しました。', error)
      }
    }

    const logout = async () => {
      try {
        await store.dispatch('logout')
      } catch (error) {
        console.error('ログアウトに失敗しました。', error)
      }
    }

    onMounted(checkLoginStatus)

    return {
      isLoggedIn,
      isAdmin,
      logout,
    }
  },
}
</script>

<style scoped>
.homepage-container {
  padding: 2em;
  background-color: #ffffff;
  color: #333;
  min-height: 100vh;
  text-align: center;
}

.header {
  margin-bottom: 2em;
}

.logo {
  font-size: 3em;
  font-family: 'Montserrat', sans-serif;
}

.catchphrase {
  margin-bottom: 2em;
}

.catchphrase h2 {
  font-size: 2.5em;
  margin-bottom: 0.5em;
  font-weight: bold;
  animation: fadeIn 1.5s ease;
}

.catchphrase p {
  font-size: 1.2em;
  animation: fadeIn 2s ease;
}

.features {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 2em;
}

.feature-box {
  width: 30%;
  min-width: 250px;
  padding: 1.5em;
  background-color: #f9f9f9;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.feature-box:hover {
  transform: scale(1.05);
}

.feature-box h3 {
  font-size: 1.5em;
  margin-bottom: 0.5em;
}

.feature-box p {
  font-size: 1em;
  line-height: 1.5;
}

.cta-section {
  margin-top: 2em;
}

.cta-button {
  padding: 0.75em 1.5em;
  margin: 0 10px;
  background-color: #ff7e5f;
  color: white;
  border: none;
  border-radius: 5px;
  text-decoration: none;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.3s;
}

.admin-button {
  background-color: #6c757d;
}

.cta-button:hover {
  background-color: #ff5e3a;
}

.admin-button:hover {
  background-color: #5a6268;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
