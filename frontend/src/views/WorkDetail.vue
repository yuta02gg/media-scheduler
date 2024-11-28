<template>
  <div class="work-detail">
    <button @click="goBack" class="back-button">戻る</button>
    <div v-if="loading" class="loading-spinner">
      <p>読み込み中...</p>
    </div>

    <div v-else>
      <h1>{{ getTitle(work) }}</h1>
      <router-link :to="`/work/${work.media_type}/${work.tmdb_id}`" class="poster-link">
        <img :src="getPosterUrl(work.poster_path)" :alt="`${getTitle(work)}のポスター画像`" class="poster-img" />
      </router-link>
      <p>公開日: {{ formatDate(getReleaseDate(work)) }}</p>
      <p class="overview">{{ work.overview }}</p>

      <div class="button-group" v-if="isLoggedIn">
        <button v-if="!isRegistered" @click="registerWork" class="register-button">登録する</button>
        <p v-if="isRegistered" class="registered-message">この作品は既に登録されています。</p>
        <button @click="openScheduleModal" class="schedule-button">スケジュールに追加</button>
        <router-link :to="`/review/${work.media_type}/${work.tmdb_id}`" class="review-link">レビューを書く</router-link>
      </div>

      <section class="reviews-section">
        <h2>レビュー</h2>
        <div v-if="reviews.length === 0">レビューがまだありません。</div>
        <ul v-else>
          <li v-for="review in reviews" :key="review.id" class="review-item">
            <div class="review-header">
              <p class="rating">評価: ★{{ review.rating }}</p>
              <p class="user">投稿者: {{ review.user?.username || '匿名' }}</p>
              <p class="date">{{ formatDateTime(review.created_at) }}</p>
            </div>
            <p class="comment">{{ review.comment }}</p>
          </li>
        </ul>
      </section>
    </div>

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
import { ref, onMounted, computed } from "vue";
import axios from "../axios";
import { useStore } from "vuex";
import { useRouter } from "vue-router";

export default {
  name: "WorkDetail",
  props: ["media_type", "id"],
  setup(props) {
    const work = ref({});
    const selectedWork = ref(null); // 追加: selectedWork を定義
    const reviews = ref([]);
    const isRegistered = ref(false);
    const loading = ref(true);
    const store = useStore();
    const router = useRouter();

    const isLoggedIn = computed(() => store.getters.isAuthenticated);
    const showScheduleModal = ref(false);
    const selectedDate = ref("");
    const modalMessage = ref("");

    const loadWork = async () => {
      try {
        const response = await axios.get(`/media/${props.media_type}/${props.id}`);
        work.value = response.data;
      } catch (error) {
        console.error("作品の取得に失敗しました:", error);
      }
    };

    const loadReviews = async () => {
      try {
        const response = await axios.get(`/media/${props.media_type}/${props.id}/reviews`);
        reviews.value = response.data.reviews || [];
      } catch (error) {
        console.error("レビューの取得に失敗しました:", error);
      }
    };

    const registerWork = async () => {
      try {
        await axios.post(`/media/${props.media_type}/${props.id}/register`);
        isRegistered.value = true;
      } catch (error) {
        console.error("作品の登録に失敗しました:", error);
        alert("作品の登録に失敗しました。再試行してください。");
      }
    };

    const openScheduleModal = () => {
      selectedWork.value = work.value; // 選択中の作品を設定
      selectedDate.value = "";
      modalMessage.value = "";
      showScheduleModal.value = true;
    };

    const closeScheduleModal = () => {
      showScheduleModal.value = false;
      selectedDate.value = "";
      modalMessage.value = "";
    };

    const addToSchedule = async () => {
      if (!selectedDate.value) {
        modalMessage.value = "日付を選択してください。";
        return;
      }

      try {
        const response = await axios.get("/schedule");
        const existingEvents = response.data;

        const isDuplicate = existingEvents.some(
          (event) =>
            event.title === getTitle(selectedWork.value) &&
            event.start === selectedDate.value
        );

        if (isDuplicate) {
          modalMessage.value = "同じ日に同じ作品が既にスケジュールに登録されています。";
          return;
        }

        await axios.post("/schedule", {
          title: getTitle(selectedWork.value),
          date: selectedDate.value,
          work_id: selectedWork.value.tmdb_id,
        });

        modalMessage.value = "スケジュールに追加しました。";
        setTimeout(() => {
          closeScheduleModal();
        }, 2000);
      } catch (error) {
        console.error("スケジュールへの追加に失敗しました。", error);
        modalMessage.value = "スケジュールへの追加に失敗しました。";
      }
    };

    const goBack = () => {
      router.back();
    };

    const getPosterUrl = (path) =>
      path ? `https://image.tmdb.org/t/p/w200${path}` : "/placeholder-image.jpg";

    const formatDate = (dateString) =>
      dateString ? new Date(dateString).toLocaleDateString("ja-JP") : "公開日不明";

    const formatDateTime = (dateString) =>
      dateString
        ? `${new Date(dateString).toLocaleDateString("ja-JP")} ${new Date(
            dateString
          ).toLocaleTimeString("ja-JP", { hour: "2-digit", minute: "2-digit" })}`
        : "不明";

    const getTitle = (work) => work.title || work.name || "タイトル不明";

    const getReleaseDate = (work) => work.release_date || work.first_air_date || "";

    const modalWorkTitle = computed(() => getTitle(selectedWork.value || {})); // 選択された作品のタイトル

    onMounted(async () => {
      loading.value = true;
      await loadWork();
      await loadReviews();

      if (isLoggedIn.value) {
        try {
          const response = await axios.get(`/media/${props.media_type}/${props.id}/is-registered`);
          isRegistered.value = response.data.isRegistered;
        } catch (error) {
          console.error("登録状態の確認に失敗しました:", error);
        }
      }

      loading.value = false;
    });

    return {
      work,
      reviews,
      selectedWork, // selectedWork を返す
      isRegistered,
      isLoggedIn,
      registerWork,
      openScheduleModal,
      closeScheduleModal,
      addToSchedule,
      showScheduleModal,
      selectedDate,
      modalMessage,
      modalWorkTitle,
      getPosterUrl,
      formatDate,
      formatDateTime,
      goBack,
      getTitle,
      getReleaseDate,
      loading,
    };
  },
};
</script>




<style scoped>
.work-detail {
  padding: 2em;
  background-color: #f9f9f9;
  border-radius: 8px;
}

/* 戻るボタンのスタイル */
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

h1 {
  font-size: 2em;
  margin-bottom: 1em;
}

/* ポスター画像のリンク */
.poster-link {
  display: inline-block;
  transition: transform 0.3s;
}

.poster-link:hover .poster-img {
  transform: scale(1.05);
}

.poster-img {
  width: 200px;
  border-radius: 8px;
  margin-bottom: 1em;
  transition: transform 0.3s;
}

.overview {
  margin-bottom: 1em;
}

/* ボタンをグループ化 */
.button-group {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 1em;
}

/* 既存の登録ボタン */
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

/* スケジュール追加ボタン */
.schedule-button {
  padding: 0.8em 1.5em;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.schedule-button:hover {
  background-color: #218838;
}

/* レビュー投稿リンクのスタイル */
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

.reviews-section {
  margin-top: 2em;
}

.reviews-section h2 {
  font-size: 1.5em;
  margin-bottom: 1em;
  color: #444;
}

ul {
  list-style-type: none;
  padding: 0;
}

.review-item {
  background-color: #ffffff;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 0.8em 1em;
  margin-bottom: 1em;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.review-header {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  margin-bottom: 0.3em;
}

.rating {
  font-weight: bold;
  color: #f39c12;
  font-size: 1.1em;
  margin-right: 0.5em;
}

.user {
  color: #333;
  font-size: 0.9em;
  margin-right: 0.5em;
}

.date {
  color: #888;
  font-size: 0.9em;
}

.comment {
  margin-top: 0.3em;
  color: #555;
  line-height: 1.5;
}

/* 読み込み中アニメーションのスタイル */
.loading-spinner {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100px;
  font-size: 1.2em;
  color: #666;
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
