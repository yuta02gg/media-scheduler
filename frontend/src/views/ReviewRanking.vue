<template>
  <div class="review-ranking">
    <h2>レビューランキング</h2>
    <div v-if="loading" class="loading">
      <p>ランキングを読み込み中...</p>
    </div>
    <div v-else>
      <div v-if="rankedWorks.length === 0">レビューがまだありません。</div>
      <ul v-else class="ranking-container">
        <li v-for="(work, index) in rankedWorks" :key="work.media_id" class="ranking-item">
          <span class="rank">第{{ index + 1 }}位</span>
          <img
            v-if="work.poster_path"
            :src="getPosterUrl(work.poster_path)"
            alt="ポスター画像"
            class="poster-img"
          />
          <div class="work-info">
            <h3 class="work-title">{{ work.title }}</h3>
            <p>レビュー数: {{ work.review_count }}</p>
            <p v-if="isNumber(work.average_rating)">
              平均評価: {{ parseFloat(work.average_rating).toFixed(1) }}
            </p>
            <p v-else>平均評価: データなし</p>
            <router-link
              :to="`/media/${work.media_type}/${work.tmdb_id}/reviews`"
              class="view-reviews-button"
            >
              レビューを見る
            </router-link>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import axios from '../axios'

export default {
  name: 'ReviewRanking',
  setup() {
    const rankedWorks = ref([])
    const loading = ref(true)

    const loadRanking = async () => {
      loading.value = true
      try {
        const response = await axios.get('/reviews/ranking')
        // average_rating を数値にキャスト
        rankedWorks.value = response.data.map((work) => {
          return {
            ...work,
            average_rating: parseFloat(work.average_rating),
          }
        })
      } catch (error) {
        console.error('ランキングの取得に失敗しました。', error)
      } finally {
        loading.value = false
      }
    }

    onMounted(loadRanking)

    const getPosterUrl = (path) => {
      return path ? `https://image.tmdb.org/t/p/w200${path}` : '/placeholder-image.jpg'
    }

    const isNumber = (value) => {
      return typeof value === 'number' && !isNaN(value)
    }

    return {
      rankedWorks,
      loading,
      getPosterUrl,
      isNumber,
    }
  },
}
</script>

<style scoped>
.review-ranking {
  padding: 2em;
}

.loading {
  text-align: center;
  font-size: 1.2em;
  color: #555;
}

.ranking-container {
  list-style-type: none;
  padding: 0;
}

.ranking-item {
  display: flex;
  align-items: center;
  background-color: #ffffff;
  border: 1px solid #ddd;
  border-radius: 10px;
  margin-bottom: 1.5em;
  padding: 1em;
  transition: transform 0.2s;
}

.ranking-item:hover {
  transform: translateY(-5px);
}

.rank {
  font-size: 1.5em;
  font-weight: bold;
  margin-right: 20px;
  color: #f39c12;
}

.poster-img {
  width: 100px;
  height: auto;
  border-radius: 8px;
  margin-right: 20px;
}

.work-info {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.work-title {
  font-size: 1.2em;
  margin-bottom: 0.5em;
}

.view-reviews-button {
  margin-top: 0.5em;
  padding: 0.5em 0;
  background-color: #007bff;
  color: white;
  border-radius: 5px;
  text-align: center;
  text-decoration: none;
  width: 150px; /* ボタンの幅を統一 */
}

.view-reviews-button:hover {
  background-color: #0056b3;
}
</style>
