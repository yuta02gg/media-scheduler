<template>
  <div class="search-container">
    <h1>作品検索</h1>
    <form @submit.prevent="searchWorks" class="search-form">
      <input v-model="query" placeholder="作品名で検索" class="form-input" />
      <!-- ソートオプションを追加 -->
      <select v-model="sortOption" class="form-select">
        <option value="">表示変更</option>
        <option value="release_date.desc">公開年が新しい順</option>
        <option value="release_date.asc">公開年が古い順</option>
      </select>
      <button type="submit" class="search-button">検索</button>
    </form>

    <div v-if="isLoading" class="loading">検索中...</div>
    <div v-else>
      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
      <p v-if="works.length === 0 && hasSearched">検索結果が見つかりませんでした。</p>
      <ul v-else class="works-grid">
        <li v-for="work in works" :key="`${work.media_type}-${work.id}`" class="work-item">
          <router-link :to="`/work/${work.media_type}/${work.id}`">
            <img
              :src="getPosterUrl(work.poster_path)"
              :alt="`${work.title || work.name}のポスター画像`"
              class="poster-img"
            />
          </router-link>
          <div class="work-info">
            <router-link :to="`/work/${work.media_type}/${work.id}`">
              <h3>{{ work.title || work.name }}</h3>
            </router-link>
            <p>公開日: {{ formatDate(work.release_date || work.first_air_date) }}</p>
            <button
              v-if="isLoggedIn && !isRegistered(work)"
              @click="registerWork(work)"
              class="register-button"
            >
              登録する
            </button>
            <p v-else-if="isRegistered(work)" class="registered-message">登録済みの作品です</p>
          </div>
        </li>
      </ul>
      <div v-if="totalPages > 1" class="pagination">
        <button @click="prevPage" :disabled="page === 1" class="pagination-button">前のページ</button>
        <span>{{ page }} / {{ totalPages }}</span>
        <button @click="nextPage" :disabled="page === totalPages" class="pagination-button">次のページ</button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import axios from '../axios'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'

export default {
  name: 'WorkSearch',
  setup() {
    const query = ref('')
    const sortOption = ref('') // ソートオプションのデータプロパティを追加
    const works = ref([])
    const page = ref(1)
    const totalPages = ref(1)
    const isLoading = ref(false)
    const errorMessage = ref('')
    const hasSearched = ref(false)
    const registeredWorks = ref([])
    const currentMode = ref('popular') // 'popular' または 'search'

    const store = useStore()
    const router = useRouter()
    const isLoggedIn = computed(() => store.getters.isAuthenticated)

    // 登録済み作品のロード
    const loadRegisteredWorks = async () => {
      try {
        const response = await axios.get('/user/registered-works')
        registeredWorks.value = response.data
      } catch (error) {
        console.error('登録済み作品の取得に失敗しました。', error)
        registeredWorks.value = []
      }
    }

    // 人気作品のロード
    const loadPopularWorks = async () => {
      isLoading.value = true
      errorMessage.value = ''
      hasSearched.value = false
      currentMode.value = 'popular'

      try {
        const response = await axios.get('/media/search', {
          params: { sort_by: sortOption.value || 'popularity.desc', page: page.value }
        })
        works.value = response.data.results || []
        totalPages.value = Math.min(response.data.total_pages || 1, 500) // TMDb APIのページ上限は500
      } catch (error) {
        console.error(error)
        errorMessage.value = '人気作品の取得に失敗しました。'
      } finally {
        isLoading.value = false
      }
    }

    // 検索処理
    const searchWorks = async () => {
      isLoading.value = true
      errorMessage.value = ''
      hasSearched.value = true
      page.value = 1

      if (!query.value.trim() && !sortOption.value) {
        // クエリとソートオプションが空の場合、人気作品を表示
        currentMode.value = 'popular'
      } else {
        currentMode.value = 'search'
      }

      await fetchWorks()
    }

    // ページネーション
    const nextPage = async () => {
      if (page.value < totalPages.value) {
        page.value++
        await fetchWorks()
      }
    }

    const prevPage = async () => {
      if (page.value > 1) {
        page.value--
        await fetchWorks()
      }
    }

    const fetchWorks = async () => {
      isLoading.value = true
      errorMessage.value = ''

      try {
        const params = { page: page.value }
        if (currentMode.value === 'search') {
          if (query.value.trim()) {
            params.query = query.value
          }
          // 検索時には sort_by パラメータを送信しない
        } else {
          params.sort_by = sortOption.value || 'popularity.desc'
        }
        const response = await axios.get('/media/search', { params })
        let results = response.data.results || []

        // クライアントサイドでソート
        if (currentMode.value === 'search' && sortOption.value) {
          results = sortWorks(results, sortOption.value)
        }

        works.value = results
        totalPages.value = Math.min(response.data.total_pages || 1, 500)
      } catch (error) {
        console.error(error)
        errorMessage.value = '作品の取得に失敗しました。'
        works.value = []
      } finally {
        isLoading.value = false
      }
    }

    // ソート関数を追加
    const sortWorks = (worksArray, sortOrder) => {
      return worksArray.slice().sort((a, b) => {
        const dateA = new Date(a.release_date || a.first_air_date)
        const dateB = new Date(b.release_date || b.first_air_date)

        if (sortOrder === 'release_date.desc') {
          // 新しい順
          return dateB - dateA
        } else if (sortOrder === 'release_date.asc') {
          // 古い順
          return dateA - dateB
        } else {
          return 0
        }
      })
    }

    onMounted(async () => {
      if (isLoggedIn.value) {
        await loadRegisteredWorks()
      }
      await loadPopularWorks()
    })

    watch(isLoggedIn, async (newVal) => {
      if (newVal) {
        await loadRegisteredWorks()
      } else {
        registeredWorks.value = []
      }
    })

    const isRegistered = (work) => {
      return registeredWorks.value.some(
        (registeredWork) =>
          registeredWork.tmdb_id === work.id && registeredWork.media_type === work.media_type
      )
    }

    const registerWork = async (work) => {
      if (!isLoggedIn.value) {
        alert('作品を登録するにはログインが必要です。')
        router.push('/login')
        return
      }

      try {
        await axios.post(`/media/${work.media_type}/${work.id}/register`)
        registeredWorks.value.push({
          tmdb_id: work.id,
          media_type: work.media_type
        })
        alert('作品を登録しました。')
      } catch (error) {
        console.error('作品の登録に失敗しました。', error)
        alert('作品の登録に失敗しました。')
      }
    }

    const getPosterUrl = (path) => {
      return path ? `https://image.tmdb.org/t/p/w200${path}` : '/placeholder-image.jpg'
    }

    const formatDate = (dateString) => {
      if (dateString) {
        const date = new Date(dateString)
        return date.toLocaleDateString('ja-JP')
      } else {
        return '公開日不明'
      }
    }

    return {
      query,
      sortOption, // ソートオプションを追加
      works,
      page,
      totalPages,
      isLoading,
      errorMessage,
      hasSearched,
      isLoggedIn,
      searchWorks,
      getPosterUrl,
      formatDate,
      nextPage,
      prevPage,
      isRegistered,
      registerWork
    }
  }
}
</script>

<style scoped>
.search-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 2em;
  background-color: white;
  min-height: 100vh;
}

h1 {
  color: #333;
  font-size: 2.5em;
  margin-bottom: 1em;
}

.search-form {
  display: flex;
  justify-content: center;
  margin-bottom: 2em;
  flex-wrap: wrap; /* フォームを折り返し可能にする */
}

.form-input,
.form-select {
  padding: 0.8em;
  font-size: 1.2em;
  border: 1px solid #ccc;
  border-radius: 4px;
  width: 250px;
  margin-right: 10px;
  margin-bottom: 10px; /* スマホ表示で間隔を空ける */
}

.search-button {
  padding: 0.8em 1.5em;
  font-size: 1.2em;
  color: white;
  background-color: #007bff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-bottom: 10px; /* スマホ表示で間隔を空ける */
}

.loading {
  color: #333;
  font-size: 1.2em;
}

.works-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 20px;
  width: 100%;
  max-width: 1200px;
}

.work-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  background-color: #f5f5f5;
  padding: 1em;
  border-radius: 8px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.poster-img {
  width: 100%;
  max-width: 180px;
  height: auto;
  border-radius: 4px;
  transition: transform 0.3s ease;
}

.poster-img:hover {
  transform: scale(1.05);
}

.work-info {
  margin-top: 1em;
  color: #333;
}

.pagination {
  display: flex;
  justify-content: center;
  gap: 1em;
  margin-top: 1em;
}

.pagination-button {
  padding: 0.8em;
  font-size: 1em;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.pagination-button:hover {
  background-color: #0056b3;
}

.pagination-button:disabled {
  background-color: #aaa;
  cursor: not-allowed;
}

.error {
  color: red;
}

.register-button {
  padding: 0.8em 1.5em;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 1em;
}

.registered-message {
  color: green;
  margin-top: 1em;
}
</style>
