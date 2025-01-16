<!-- src/views/WorkSearch.vue -->
<template>
  <div class="search-container">
    <h1>作品検索</h1>
    <form @submit.prevent="searchWorks" class="search-form">
      <input v-model="query" placeholder="作品名で検索" class="form-input" />
      
      <!-- メディアタイプオプション -->
      <select v-model="mediaType" class="form-select">
        <option value="">すべて</option>
        <option value="movie">映画</option>
        <option value="tv">テレビ</option>
      </select>
      
      <!-- ソートオプション -->
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
      <p v-if="filteredWorks.length === 0 && hasSearched">
        検索結果が見つかりませんでした。
      </p>
      <ul v-else class="works-grid">
        <li
          v-for="work in filteredWorks"
          :key="`${work.media_type || 'unknown'}-${work.id}`"
          class="work-item"
        >
          <!-- ルートURLには tmdb_id を使う -->
          <router-link :to="`/media/${work.media_type}/${work.tmdb_id}`">
            <img
              :src="getPosterUrl(work.poster_path)"
              :alt="`${work.title || work.name}のポスター画像`"
              class="poster-img"
            />
          </router-link>
          <div class="work-info">
            <router-link :to="`/media/${work.media_type}/${work.tmdb_id}`">
              <h3>{{ work.title || work.name }}</h3>
            </router-link>
            <p>公開日: {{ formatDate(work.release_date || work.first_air_date) }}</p>

            <!-- 作品登録ボタン (DBのIDを使う) -->
            <button
              v-if="isLoggedIn && !isRegistered(work.media_type, work.id)"
              @click="registerWork(work)"
              class="register-button"
            >
              登録する
            </button>
            <p
              v-else-if="isRegistered(work.media_type, work.id)"
              class="registered-message"
            >
              登録済みの作品です
            </p>
          </div>
        </li>
      </ul>

      <!-- ページネーション -->
      <div v-if="totalPages > 1" class="pagination">
        <button
          @click="prevPage"
          :disabled="page === 1"
          class="pagination-button"
        >
          前のページ
        </button>
        <span>{{ page }} / {{ totalPages }}</span>
        <button
          @click="nextPage"
          :disabled="page === totalPages"
          class="pagination-button"
        >
          次のページ
        </button>
      </div>

      <p
        v-if="currentMode === 'search' && sortOption.value"
        class="info"
      >
        現在のソートはページ内のみ適用されています。全体的な並び替えはサーバーサイドでサポートされていません。
      </p>
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
    const mediaType = ref('') 
    const sortOption = ref('')
    const works = ref([])
    const page = ref(1)
    const totalPages = ref(1)
    const isLoading = ref(false)
    const errorMessage = ref('')
    const hasSearched = ref(false)
    const currentMode = ref('search')

    const store = useStore()
    const router = useRouter()
    const isLoggedIn = computed(() => store.getters.isAuthenticated)

    // メディアタイプがある作品のみフィルタ
    const filteredWorks = computed(() => {
      return works.value.filter(work => work.media_type)
    })

    // 検索
    const searchWorks = async () => {
      isLoading.value = true
      errorMessage.value = ''
      hasSearched.value = true
      page.value = 1
      currentMode.value = 'search'
      await fetchWorks()
    }

    // 次のページ
    const nextPage = async () => {
      if (page.value < totalPages.value) {
        page.value++
        await fetchWorks()
      }
    }

    // 前のページ
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
        const params = {
          page: page.value,
          media_type: mediaType.value || undefined,
          query: query.value.trim() || '',
        }
        // もしサーバーサイドで sort_by を受け取らないならクライアント側でソート
        const response = await axios.get('/media/search', { params })
        let results = response.data.results || []

        // フロントでは "tmdb_id" を使ってルートを組み立てるので、マッピングを追加する。
        results = results.map(item => {
          return {
            ...item,
            tmdb_id: item.id    // TMDb ID を tmdb_id キーにコピー
            // ここで item.id は「DBのID」ではなく「TMDbのID」である点に注意
          }
        })


        if (sortOption.value) {
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

    // クライアントサイドソート
    const sortWorks = (worksArray, sortOrder) => {
      return worksArray.slice().sort((a, b) => {
        const dateA = new Date(a.release_date || a.first_air_date)
        const dateB = new Date(b.release_date || b.first_air_date)
        if (sortOrder === 'release_date.desc') {
          return dateB - dateA
        } else if (sortOrder === 'release_date.asc') {
          return dateA - dateB
        } else {
          return 0
        }
      })
    }

    onMounted(() => {
      searchWorks()
    })

    watch(isLoggedIn, async (newVal) => {
      if (newVal) {
        await store.dispatch('loadRegisteredWorks')
      }
    })

    // DBのidを使って判定
    const isRegistered = (media_type, workId) => {
      return store.getters.isRegistered(media_type, workId)
    }

    // DBのidを使って登録
    const registerWork = async (work) => {
      if (!isLoggedIn.value) {
        alert('作品を登録するにはログインが必要です。')
        router.push('/login')
        return
      }
      try {
        await store.dispatch('registerWork', {
          media_id: work.id,         // ★ここがDBのID
          media_type: work.media_type
        })
        alert('作品を登録しました。')
      } catch (error) {
        console.error('作品の登録に失敗しました。', error)
        alert('作品の登録に失敗しました。')
      }
    }

    const getPosterUrl = (path) => {
      return path
        ? `https://image.tmdb.org/t/p/w200${path}`
        : '/placeholder-image.jpg'
    }

    const formatDate = (dateString) => {
      if (dateString) {
        const date = new Date(dateString)
        return date.toLocaleDateString('ja-JP')
      }
      return '公開日不明'
    }

    return {
      query,
      mediaType,
      sortOption,
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
      registerWork,
      currentMode,
      filteredWorks,
    }
  },
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
  flex-wrap: wrap;
}

.form-input,
.form-select {
  padding: 0.8em;
  font-size: 1.2em;
  border: 1px solid #ccc;
  border-radius: 4px;
  width: 250px;
  margin-right: 10px;
  margin-bottom: 10px;
}

.search-button {
  padding: 0.8em 1.5em;
  font-size: 1.2em;
  color: white;
  background-color: #007bff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-bottom: 10px;
}

.loading {
  color: #333;
  font-size: 1.2em;
}

.info {
  color: #555;
  margin-top: 1em;
  font-size: 1em;
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

.register-button:hover {
  background-color: #0056b3;
}

.registered-message {
  color: green;
  margin-top: 1em;
}
</style>
