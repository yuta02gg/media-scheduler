<template>
  <div class="admin-review-management">
    <h2>ユーザーのレビュー管理</h2>
    
    <!-- レビューフィルタセクション -->
    <div class="filter-section">
      <input
        type="text"
        v-model="filterUsername"
        placeholder="ユーザー名で検索"
        class="filter-input"
      />
      <input
        type="text"
        v-model="filterMediaTitle"
        placeholder="作品タイトルで検索"
        class="filter-input"
      />
      <select v-model="filterRatingMin" class="filter-select">
        <option value="">最低評価</option>
        <option v-for="n in 5" :key="'min-' + n" :value="n">{{ n }} 星以上</option>
      </select>
      <select v-model="filterRatingMax" class="filter-select">
        <option value="">最高評価</option>
        <option v-for="n in 5" :key="'max-' + n" :value="n">{{ n }} 星以下</option>
      </select>
      <input
        type="date"
        v-model="filterDateFrom"
        placeholder="投稿日（開始）"
        class="filter-input"
      />
      <input
        type="date"
        v-model="filterDateTo"
        placeholder="投稿日（終了）"
        class="filter-input"
      />
      <button @click="applyFilters" class="filter-button">フィルタ適用</button>
      <button @click="resetFilters" class="reset-button">リセット</button>
    </div>
    
    <div v-if="loading" class="loading">
      <p>レビュー一覧を読み込み中...</p>
    </div>
    <div v-else>
      <div v-if="reviews.length === 0">投稿されたレビューはありません。</div>
      <table v-else class="review-table">
        <thead>
          <tr>
            <th>レビューID</th>
            <th>作品タイトル</th>
            <th>投稿ユーザー</th>
            <th>評価</th>
            <th>投稿日</th>
            <th>コメント</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="review in reviews" :key="review.id">
            <td>{{ review.id }}</td>
            <td>{{ review.media_title }}</td>
            <td>{{ review.username }}</td>
            <td>{{ review.rating }} 星</td>
            <td>{{ formatDate(review.created_at) }}</td>
            <td>{{ review.comment }}</td>
            <td>
              <button @click="deleteReview(review.id)" class="delete-button">削除</button>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
    </div>
    
    <!-- レビュー削除確認モーダル -->
    <div v-if="showDeleteModal" class="modal">
      <div class="modal-content">
        <span class="close" @click="closeDeleteModal">&times;</span>
        <h3>レビュー削除確認</h3>
        <p>本当にこのレビューを削除しますか？</p>
        <button @click="confirmDelete" class="confirm-button">はい</button>
        <button @click="closeDeleteModal" class="cancel-button">いいえ</button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import axios from '../axios'
import { useRoute } from 'vue-router'

export default {
  name: 'AdminReviewManagement',
  setup() {
    const route = useRoute()
    const userId = ref(route.params.id)

    const reviews = ref([])
    const loading = ref(true)
    const errorMessage = ref('')

    // フィルタリング用のリファレンス
    const filterUsername = ref('')
    const filterMediaTitle = ref('')
    const filterRatingMin = ref('')
    const filterRatingMax = ref('')
    const filterDateFrom = ref('')
    const filterDateTo = ref('')

    // レビュー削除用
    const showDeleteModal = ref(false)
    const reviewToDelete = ref(null)

    const loadReviews = async (filters = {}) => {
      loading.value = true
      try {
        const params = { ...filters, user_id: userId.value } // ユーザーIDをフィルタに追加
        const response = await axios.get(`/admin/users/${userId.value}/reviews`, { params })
        reviews.value = response.data
        errorMessage.value = ''
      } catch (error) {
        console.error('レビュー一覧の取得に失敗しました。', error)
        errorMessage.value = 'レビュー一覧の取得に失敗しました。'
      } finally {
        loading.value = false
      }
    }

    const deleteReview = (reviewId) => {
      reviewToDelete.value = reviewId
      showDeleteModal.value = true
    }

    const confirmDelete = async () => {
      try {
        await axios.delete(`/admin/reviews/${reviewToDelete.value}`)
        reviews.value = reviews.value.filter(review => review.id !== reviewToDelete.value)
        errorMessage.value = ''
        alert('レビューが削除されました。')
      } catch (error) {
        console.error('レビューの削除に失敗しました。', error)
        errorMessage.value = 'レビューの削除に失敗しました。'
      } finally {
        showDeleteModal.value = false
        reviewToDelete.value = null
      }
    }

    const closeDeleteModal = () => {
      showDeleteModal.value = false
      reviewToDelete.value = null
    }

    const formatDate = (dateStr) => {
      if (!dateStr) return ''
      const date = new Date(dateStr)
      return `${date.getFullYear()}-${(date.getMonth()+1).toString().padStart(2,'0')}-${date.getDate().toString().padStart(2,'0')}`
    }

    const applyFilters = () => {
      const filters = {}
      if (filterUsername.value) filters.username = filterUsername.value
      if (filterMediaTitle.value) filters.media_title = filterMediaTitle.value
      if (filterRatingMin.value) filters.rating_min = filterRatingMin.value
      if (filterRatingMax.value) filters.rating_max = filterRatingMax.value
      if (filterDateFrom.value) filters.date_from = filterDateFrom.value
      if (filterDateTo.value) filters.date_to = filterDateTo.value
      loadReviews(filters)
    }

    const resetFilters = () => {
      filterUsername.value = ''
      filterMediaTitle.value = ''
      filterRatingMin.value = ''
      filterRatingMax.value = ''
      filterDateFrom.value = ''
      filterDateTo.value = ''
      loadReviews()
    }

    onMounted(() => loadReviews())

    return {
      reviews,
      loading,
      errorMessage,
      filterUsername,
      filterMediaTitle,
      filterRatingMin,
      filterRatingMax,
      filterDateFrom,
      filterDateTo,
      applyFilters,
      resetFilters,
      deleteReview,
      showDeleteModal,
      confirmDelete,
      closeDeleteModal,
      formatDate,
      reviewToDelete,
    }
  }
}
</script>

<style scoped>
.admin-review-management {
  padding: 2em;
}

.loading {
  text-align: center;
  font-size: 1.2em;
  color: #555;
}

.filter-section {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5em;
  margin-bottom: 1em;
}

.filter-input,
.filter-select {
  padding: 0.5em;
  border: 1px solid #ccc;
  border-radius: 3px;
  flex: 1;
  min-width: 150px;
}

.filter-button,
.reset-button {
  padding: 0.5em 1em;
  background-color: #007bff;
  color: #fff;
  border: none;
  border-radius: 3px;
  cursor: pointer;
}

.reset-button {
  background-color: #6c757d;
}

.filter-button:hover {
  background-color: #0069d9;
}

.reset-button:hover {
  background-color: #5a6268;
}

.review-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1em;
}

.review-table th,
.review-table td {
  border: 1px solid #ddd;
  padding: 0.5em;
  text-align: left;
}

.review-table th {
  background-color: #f5f5f5;
}

.delete-button {
  background-color: #dc3545;
  color: #fff;
  border: none;
  border-radius: 3px;
  padding: 0.5em 1em;
  cursor: pointer;
}

.delete-button:hover {
  background-color: #c82333;
}

.error-message {
  color: red;
  margin-top: 1em;
  font-weight: bold;
}

/* モーダルのスタイル */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background-color: #fff;
  padding: 2em;
  border-radius: 5px;
  width: 400px;
  position: relative;
}

.close {
  position: absolute;
  top: 0.5em;
  right: 1em;
  font-size: 1.5em;
  cursor: pointer;
}

.confirm-button {
  padding: 0.5em 1em;
  background-color: #dc3545;
  color: #fff;
  border: none;
  border-radius: 3px;
  cursor: pointer;
  margin-right: 0.5em;
}

.confirm-button:hover {
  background-color: #c82333;
}

.cancel-button {
  padding: 0.5em 1em;
  background-color: #6c757d;
  color: #fff;
  border: none;
  border-radius: 3px;
  cursor: pointer;
}

.cancel-button:hover {
  background-color: #5a6268;
}
</style>
