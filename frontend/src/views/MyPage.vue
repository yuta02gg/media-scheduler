<template>
  <div class="mypage">
    <h1>マイページ</h1>
    <h2>登録済みの作品</h2>
    <div v-if="loading" class="loading">
      <p>読み込み中...</p>
    </div>
    <ul v-else-if="registeredWorks.length > 0" class="works-list">
      <li v-for="work in registeredWorks" :key="work.id" class="work-item">
        <!-- 画像をリンクに変更してホバーアニメーションを追加 -->
        <router-link :to="`/work/${work.media_type}/${work.tmdb_id}`" class="poster-link">
          <img :src="getPosterUrl(work.poster_path)" :alt="`${getTitle(work)}のポスター画像`" class="poster-img" />
        </router-link>
        <div class="work-info">
          <router-link :to="`/work/${work.media_type}/${work.tmdb_id}`" class="work-title">{{ getTitle(work) }}</router-link>
          <!-- レビュー投稿へのリンク -->
          <router-link :to="`/review/${work.media_type}/${work.tmdb_id}`" class="review-link">レビューを書く</router-link>
          <!-- スケジュールに追加ボタン -->
          <button @click="openScheduleModal(work)" class="schedule-button">スケジュールに追加</button>
        </div>
      </li>
    </ul>
    <p v-else>登録済みの作品はありません。</p>

    <!-- スケジュール追加モーダル -->
    <div v-if="showScheduleModal" class="modal-overlay">
      <div class="modal-content">
        <h3>{{ modalWorkTitle }}をスケジュールに追加</h3>
        <label for="schedule-date">日付を選択：</label>
        <input type="date" id="schedule-date" v-model="selectedDate" />
        <div class="modal-buttons">
          <button @click="addToSchedule" class="add-button">スケジュールに追加</button>
          <button @click="closeScheduleModal" class="close-button">キャンセル</button>
        </div>
        <p v-if="modalMessage" class="modal-message">{{ modalMessage }}</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import axios from '../axios'

export default {
  name: 'MyPage',
  setup() {
    const registeredWorks = ref([])
    const loading = ref(true)
    const errorMessage = ref('')
    const showScheduleModal = ref(false)
    const selectedDate = ref('')
    const selectedWork = ref(null)
    const modalMessage = ref('')

    const loadRegisteredWorks = async () => {
      try {
        const response = await axios.get('/user/registered-works')
        registeredWorks.value = response.data
      } catch (error) {
        console.error('登録済み作品の取得に失敗しました。', error)
        errorMessage.value = '登録済み作品の取得に失敗しました。'
      } finally {
        loading.value = false
      }
    }

    const getPosterUrl = (path) => {
      return path ? `https://image.tmdb.org/t/p/w200${path}` : '/placeholder-image.jpg'
    }

    const getTitle = (work) => {
      return work.title || work.name || 'タイトル不明'
    }

    const openScheduleModal = (work) => {
      selectedWork.value = work
      selectedDate.value = ''
      modalMessage.value = ''
      showScheduleModal.value = true
    }

    const closeScheduleModal = () => {
      showScheduleModal.value = false
      selectedWork.value = null
      selectedDate.value = ''
      modalMessage.value = ''
    }

    const addToSchedule = async () => {
      if (!selectedDate.value) {
        modalMessage.value = '日付を選択してください。'
        return
      }

      try {
        // 重複チェックのために現在のスケジュールを取得
        const response = await axios.get('/schedule')
        const existingEvents = response.data

        const isDuplicate = existingEvents.some(
          (event) =>
            event.title === getTitle(selectedWork.value) && event.start === selectedDate.value
        )

        if (isDuplicate) {
          modalMessage.value = '同じ日に同じ作品が既にスケジュールに登録されています。'
          return
        }

        // スケジュールに追加
        await axios.post('/schedule', {
          title: getTitle(selectedWork.value),
          date: selectedDate.value,
          work_id: selectedWork.value.id,
        })

        modalMessage.value = 'スケジュールに追加しました。'
        setTimeout(() => {
          closeScheduleModal()
        }, 2000)
      } catch (error) {
        console.error('スケジュールへの追加に失敗しました。', error)
        modalMessage.value = 'スケジュールへの追加に失敗しました。'
      }
    }

    const modalWorkTitle = computed(() => {
      return selectedWork.value ? getTitle(selectedWork.value) : ''
    })

    onMounted(() => {
      loadRegisteredWorks()
    })

    return {
      registeredWorks,
      loading,
      errorMessage,
      getPosterUrl,
      getTitle,
      showScheduleModal,
      selectedDate,
      openScheduleModal,
      closeScheduleModal,
      addToSchedule,
      modalMessage,
      modalWorkTitle,
    }
  },
}
</script>

<style scoped>
/* マイページのスタイル */
.mypage {
  padding: 2em;
}

.works-list {
  list-style-type: none;
  padding: 0;
}

.work-item {
  display: flex;
  align-items: center;
  background-color: #ffffff;
  border: 1px solid #ddd;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5em;
  padding: 1em;
  transition: transform 0.2s;
}

.poster-link {
  display: inline-block;
  transition: transform 0.3s;
}

.poster-link:hover .poster-img {
  transform: scale(1.05);
}

.poster-img {
  width: 100px;
  height: auto;
  border-radius: 8px;
  transition: transform 0.3s;
}

.work-info {
  display: flex;
  flex-direction: column;
  margin-left: 20px;
}

.work-title {
  font-size: 1.2em;
  font-weight: bold;
  color: #333;
  margin-bottom: 0.5em;
  text-decoration: none;
}

.work-title:hover {
  color: #007bff;
}

/* ローディング表示 */
.loading {
  text-align: center;
  font-size: 1.2em;
  color: #555;
}

/* レビューリンクのスタイル */
.review-link {
  margin-top: 0.5em;
  padding: 0.5em 1em;
  background-color: #007bff;
  color: white;
  border-radius: 5px;
  text-decoration: none;
  text-align: center;
  width: fit-content;
  transition: background-color 0.2s;
}

.review-link:hover {
  background-color: #0056b3;
}

/* スケジュールボタンのスタイル */
.schedule-button {
  margin-top: 0.5em;
  padding: 0.5em 1em;
  background-color: #28a745;
  color: white;
  border-radius: 5px;
  border: none;
  cursor: pointer;
  width: fit-content;
  transition: background-color 0.2s;
}

.schedule-button:hover {
  background-color: #218838;
}

/* モーダルのスタイル */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  width: 90%;
  max-width: 400px;
  text-align: center;
}

.modal-buttons {
  margin-top: 20px;
  display: flex;
  justify-content: center;
}

.add-button, .close-button {
  margin: 0 10px;
  padding: 0.5em 1.5em;
  font-size: 1em;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.add-button {
  background-color: #28a745;
}

.add-button:hover {
  background-color: #218838;
}

.close-button {
  background-color: #dc3545;
}

.close-button:hover {
  background-color: #c82333;
}

.modal-message {
  margin-top: 15px;
  color: #333;
}
</style>
