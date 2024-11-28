<template>
  <div class="review-post">
    <h2>レビューを投稿</h2>
    <form @submit.prevent="submitReview" class="review-form">
      <div class="input-group">
        <label for="work">作品を選択:</label>
        <select v-model="selectedWork" id="work" required class="form-select">
          <option disabled value="">選択してください</option>
          <option v-for="work in registeredWorks" :key="work.id" :value="work">
            {{ getTitle(work) }}
          </option>
        </select>
      </div>
      <div class="input-group">
        <label for="rating">評価:</label>
        <select v-model="rating" id="rating" required class="form-select">
          <option disabled value="">選択してください</option>
          <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
        </select>
      </div>
      <div class="input-group">
        <label for="comment">コメント:</label>
        <textarea v-model="comment" id="comment" placeholder="コメントを入力" class="form-textarea"></textarea>
      </div>
      <button type="submit" class="submit-button">投稿する</button>
    </form>
    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import axios from '../axios'
import { useRoute } from 'vue-router'

export default {
  name: 'ReviewPost',
  props: ['media_type', 'id'],
  setup(props) {
    const registeredWorks = ref([])
    const selectedWork = ref('')
    const route = useRoute()
    const mediaType = props.media_type || route.params.media_type
    const mediaId = props.id || route.params.id
    const rating = ref('')
    const comment = ref('')
    const errorMessage = ref('')
    const loading = ref(true)

    const loadRegisteredWorks = async () => {
      try {
        const response = await axios.get('/user/registered-works')
        registeredWorks.value = response.data

        // mediaType と mediaId が提供されている場合、対応する作品を選択
        if (mediaType && mediaId) {
          const preSelectedWork = registeredWorks.value.find(
            (work) => work.media_type === mediaType && work.tmdb_id == mediaId
          )
          if (preSelectedWork) {
            selectedWork.value = preSelectedWork
          }
        }
      } catch (error) {
        console.error('登録済み作品の取得に失敗しました。', error)
        errorMessage.value = '登録済み作品の取得に失敗しました。'
      } finally {
        loading.value = false
      }
    }

    const submitReview = async () => {
      if (!selectedWork.value) {
        errorMessage.value = '作品を選択してください。'
        return
      }

      try {
        await axios.post(
          `/media/${selectedWork.value.media_type}/${selectedWork.value.tmdb_id}/reviews`,
          {
            rating: rating.value,
            comment: comment.value
          }
        )
        // フォームをリセット
        selectedWork.value = ''
        rating.value = ''
        comment.value = ''
        errorMessage.value = ''
        alert('レビューを投稿しました。')
      } catch (error) {
        if (error.response && error.response.status === 422) {
          errorMessage.value = '入力内容を確認してください。'
        } else {
          errorMessage.value = 'レビューの投稿に失敗しました。'
        }
        console.error('レビュー投稿エラー:', error)
      }
    }

    const getTitle = (work) => {
      return work.title || work.name || 'タイトル不明'
    }

    onMounted(() => {
      loadRegisteredWorks()
    })

    return {
      registeredWorks,
      selectedWork,
      rating,
      comment,
      errorMessage,
      loading,
      submitReview,
      getTitle
    }
  }
}
</script>

<style scoped>
.review-post {
  margin-top: 2em;
}

/* 既存のスタイルをそのまま使用 */
.review-form {
  display: flex;
  flex-direction: column;
}

.input-group {
  margin-bottom: 1em;
}

.label {
  font-weight: bold;
  margin-bottom: 0.5em;
}

.form-select,
.form-textarea {
  width: 100%;
  padding: 0.8em;
  font-size: 1em;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.form-textarea {
  min-height: 100px;
}

.submit-button {
  padding: 0.8em 1.5em;
  font-size: 1em;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.submit-button:hover {
  background-color: #218838;
}

.error {
  color: red;
}
</style>
