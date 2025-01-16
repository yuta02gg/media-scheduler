<!-- src/views/WorkDetail.vue -->
<template>
  <div class="work-detail-container">
    <button @click="goBack" class="back-button">戻る</button>
    
    <div v-if="isLoading" class="loading-spinner">読み込み中...</div>
    <div v-else-if="errorMessage" class="error">{{ errorMessage }}</div>
    <div v-else>
      <div class="work-detail">
        <img
          :src="getPosterUrl(work.poster_path)"
          :alt="`${work.title || work.name}のポスター画像`"
          class="poster-img"
        />
        <div class="detail-info">
          <h1>{{ work.title || work.name }}</h1>
          <p><strong>公開日:</strong> {{ formatDate(work.release_date || work.first_air_date) }}</p>
          <p><strong>あらすじ:</strong> {{ work.overview || 'あらすじはありません。' }}</p>
          
          <div class="button-group">
            <!-- DBのIDで登録判定 -->
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
            
            <!-- レビューを見る (ルートURLは tmdb_id) -->
            <router-link
              :to="`/media/${work.media_type}/${work.tmdb_id}/reviews`"
              class="review-link"
            >
              レビューを見る
            </router-link>
          </div>
        </div>
      </div>
      
      <!-- 他のコンテンツがあれば追加 -->
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from '../axios'
import { useStore } from 'vuex'

export default {
  name: 'WorkDetail',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const store = useStore()

    const work = ref(null)
    const isLoading = ref(true)
    const errorMessage = ref('')
    const isLoggedIn = computed(() => store.getters.isAuthenticated)

    // 作品詳細の取得
    const fetchWorkDetail = async () => {
      isLoading.value = true
      errorMessage.value = ''

      const { media_type, tmdb_id } = route.params
      if (!media_type || !tmdb_id) {
         errorMessage.value = '無効な作品IDです。（media_type または tmdb_id がありません）'
         isLoading.value = false
         return
       }

      try {
        // バックエンドで tmdb_id をキーに検索し、
        // DBのID (id) も含めて返してもらう
        const response = await axios.get(`/media/${media_type}/${tmdb_id}`)
        work.value = response.data
      } catch (error) {
        console.error('作品詳細の取得に失敗しました。', error)
        if (error.response && error.response.status === 404) {
          errorMessage.value = '指定された作品が見つかりませんでした。'
        } else if (error.response && error.response.status === 401) {
          errorMessage.value = '認証が必要です。ログインしてください。'
        } else {
          errorMessage.value = '作品詳細の取得に失敗しました。'
        }
      } finally {
        isLoading.value = false
      }
    }

    onMounted(fetchWorkDetail)

    // DBのIDで判定
    const isRegistered = (media_type, dbId) => {
      return store.getters.isRegistered(media_type, dbId)
    }

    // DBのIDで登録
    const registerWork = async (workItem) => {
      if (!isLoggedIn.value) {
        alert('作品を登録するにはログインが必要です。')
        router.push('/login')
        return
      }
      try {
        // DBのIDを送る
        await store.dispatch('registerWork', {
          tmdb_id: workItem.tmdb_id,
          media_type: workItem.media_type
        })
        alert('作品を登録しました。')
      } catch (error) {
        console.error('作品の登録に失敗しました。', error)
        alert('作品の登録に失敗しました。')
      }
    }

    const getPosterUrl = (path) => {
      return path
        ? `https://image.tmdb.org/t/p/w300${path}`
        : '/placeholder-image.jpg'
    }

    const formatDate = (dateString) => {
      if (dateString) {
        const date = new Date(dateString)
        return date.toLocaleDateString('ja-JP')
      }
      return '公開日不明'
    }

    const goBack = () => {
      router.back()
    }

    return {
      work,
      isLoading,
      errorMessage,
      isLoggedIn,
      isRegistered,
      registerWork,
      getPosterUrl,
      formatDate,
      goBack
    }
  }
}
</script>

<style scoped>
.work-detail-container {
  padding: 2em;
  background-color: white;
  min-height: 100vh;
}

.back-button {
  padding: 0.8em 1.5em;
  background-color: #ccc;
  color: #333;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-bottom: 1em;
}

.back-button:hover {
  background-color: #aaa;
}

.work-detail {
  display: flex;
  flex-direction: column;
  align-items: center;
  background-color: #f9f9f9;
  padding: 2em;
  border-radius: 8px;
}

.poster-img {
  width: 300px;
  border-radius: 8px;
  margin-bottom: 1em;
  transition: transform 0.3s;
}

.poster-img:hover {
  transform: scale(1.05);
}

.detail-info {
  max-width: 800px;
  text-align: left;
}

.detail-info h1 {
  font-size: 2em;
  margin-bottom: 0.5em;
}

.detail-info p {
  font-size: 1.1em;
  margin-bottom: 0.5em;
}

.button-group {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 1em;
}

.register-button {
  padding: 0.8em 1.5em;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.register-button:hover {
  background-color: #0056b3;
}

.review-link {
  padding: 0.8em 1.5em;
  background-color: #ffc107;
  color: #212529;
  border-radius: 4px;
  text-decoration: none;
  text-align: center;
}

.review-link:hover {
  background-color: #e0a800;
}

.registered-message {
  color: green;
  margin-top: 1em;
}

.loading-spinner {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100px;
  font-size: 1.2em;
  color: #666;
}

.error {
  color: red;
  text-align: center;
  font-size: 1.2em;
}
</style>
