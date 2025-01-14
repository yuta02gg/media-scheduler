<template>
  <div class="review-list">
    <div v-if="loading" class="loading">
      <p>レビューを読み込み中...</p>
    </div>
    <div v-else>
      <!-- 前に戻るボタンを追加 -->
      <button class="back-button" @click="goBack">前に戻る</button>

      <div class="media-info">
        <img
          v-if="media.poster_path"
          :src="getPosterUrl(media.poster_path)"
          alt="ポスター画像"
          class="poster-img"
        />
        <div class="media-details">
          <h2 class="media-title">{{ media.title }}</h2>
          <p class="media-overview">{{ media.overview }}</p>
        </div>
      </div>

      <div v-if="reviews.length === 0">
        この作品のレビューはまだありません。
      </div>
      <ul v-else class="reviews-container">
        <li
          v-for="review in reviews"
          :key="review.id"
          class="review-item"
        >
          <div class="review-content">
            <p class="rating">評価: {{ review.rating }}</p>
            <p class="comment">{{ review.comment }}</p>
            <p class="user">
              投稿者: {{ review.user ? review.user.username : '匿名' }}
            </p>
            <p class="date">投稿日: {{ formatDate(review.created_at) }}</p>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from '../axios'

export default {
  name: 'ReviewList',
  setup() {
    const route = useRoute()
    const router = useRouter()

    const mediaType = ref(route.params.media_type || null)
    const tmdb_id = ref(route.params.tmdb_id || null)
    const reviews = ref([])
    const media = ref({})
    const loading = ref(true)

    // レビュー読み込み
    const loadReviews = async () => {
      loading.value = true
      try {
        if (mediaType.value && tmdb_id.value) {
          const response = await axios.get(
            `/media/${mediaType.value}/${tmdb_id.value}/reviews`
          )
          reviews.value = response.data.reviews || []
          media.value = response.data.media || {}
        } else {
          console.error('メディアタイプまたはIDが指定されていません。')
        }
      } catch (error) {
        console.error('レビューの取得に失敗しました。', error)
      } finally {
        loading.value = false
      }
    }

    // 前に戻る
    const goBack = () => {
      router.push('/reviews')
    }

    // ルートパラメータが変わった場合の再取得
    watch(
      () => route.params,
      (newParams) => {
        mediaType.value = newParams.media_type || null
        tmdb_id.value = newParams.tmdb_id || null
        loadReviews()
      }
    )

    const formatDate = (dateString) => {
      if (dateString) {
        const date = new Date(dateString)
        return date.toLocaleDateString('ja-JP')
      }
      return '日付不明'
    }

    const getPosterUrl = (path) =>
      path ? `https://image.tmdb.org/t/p/w200${path}` : '/placeholder-image.jpg'

    onMounted(loadReviews)

    return {
      reviews,
      media,
      loading,
      goBack,
      formatDate,
      getPosterUrl,
    }
  },
}
</script>

<style scoped>
.review-list {
  padding: 2em;
}

.loading {
  text-align: center;
  font-size: 1.2em;
  color: #555;
}

/* 前に戻るボタン */
.back-button {
  background-color: #007bff;
  color: #fff;
  padding: 0.5em 1em;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  margin-bottom: 1em;
}

.back-button:hover {
  background-color: #0056b3;
}

.media-info {
  display: flex;
  align-items: flex-start;
  margin-bottom: 2em;
}

.poster-img {
  width: 150px;
  height: auto;
  border-radius: 8px;
  margin-right: 20px;
}

.media-details {
  flex: 1;
}

.media-title {
  font-size: 2em;
  margin-bottom: 0.5em;
}

.media-overview {
  font-size: 1em;
  color: #666;
}

.reviews-container {
  list-style-type: none;
  padding: 0;
}

.review-item {
  background-color: #ffffff;
  border: 1px solid #ddd;
  border-radius: 10px;
  margin-bottom: 1.5em;
  padding: 1em;
}

.review-content {
  display: flex;
  flex-direction: column;
}

.rating {
  font-weight: bold;
  color: #f39c12;
  font-size: 1.1em;
  margin-bottom: 0.5em;
}

.comment {
  margin-bottom: 0.5em;
  color: #333;
  line-height: 1.4;
}

.user,
.date {
  font-size: 0.9em;
  color: #555;
}
</style>
