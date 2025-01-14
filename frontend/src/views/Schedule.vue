<template>
  <div class="schedule-container">
    <div class="calendar-section">
      <h1>スケジュール管理</h1>
      <FullCalendar
        ref="fullCalendar"
        :options="calendarOptions"
      />
    </div>
    <div class="works-section">
      <h2>登録作品</h2>
      <div v-if="isLoading" class="loading">読み込み中...</div>
      <div v-else>
        <p v-if="registeredWorks.length === 0">
          登録された作品はありません。
        </p>
        <ul class="works-list">
          <li
            v-for="work in registeredWorks"
            :key="work.id"
            class="work-item"
            :data-event="JSON.stringify(getEventData(work))"
          >
            <img
              :src="getPosterUrl(work.poster_path)"
              :alt="work.title"
              class="poster-img"
            />
            <p>{{ work.title }}</p>
          </li>
        </ul>
      </div>
    </div>

    <!-- イベント詳細モーダル -->
    <div v-if="showEventModal" class="modal-overlay">
      <div class="modal-content">
        <h3>{{ selectedEvent.title }}</h3>
        <p class="modal-overview">
          {{ selectedEvent.extendedProps.overview }}
        </p>
        <div class="modal-buttons">
          <button @click="deleteEvent" class="delete-button">削除</button>
          <button @click="closeEventModal" class="close-modal-button">閉じる</button>
        </div>
      </div>
    </div>

    <!-- 日付クリック時の作品選択モーダル -->
    <div v-if="showDateModal" class="modal-overlay">
      <div class="modal-content">
        <h3>作品を選択</h3>
        <p>選択した日付: {{ selectedDate }}</p>
        <ul class="modal-works-list">
          <li
            v-for="work in registeredWorks"
            :key="work.id"
            class="modal-work-item"
          >
            <p>{{ work.title || work.name }}</p>
            <button
              @click="addEventToCalendar(work)"
              class="select-button"
            >
              選択する
            </button>
          </li>
        </ul>
        <button @click="closeDateModal" class="close-modal-button">閉じる</button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, nextTick } from 'vue'
import axios from '../axios'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin, { Draggable } from '@fullcalendar/interaction'

export default {
  name: 'ScheduleManagement',
  components: {
    FullCalendar,
  },
  setup() {
    const fullCalendar = ref(null)
    const events = ref([])
    const registeredWorks = ref([])
    const isLoading = ref(true)
    const showEventModal = ref(false)
    const selectedEvent = ref(null)
    const showDateModal = ref(false)
    const selectedDate = ref('')

    // スケジュール取得
    const fetchEvents = async () => {
      try {
        const response = await axios.get('/schedule')
        events.value = response.data
        updateCalendarEvents()
      } catch (error) {
        console.error('スケジュール取得エラー:', error)
      }
    }

    // カレンダーイベントを更新
    const updateCalendarEvents = () => {
      if (fullCalendar.value) {
        const calendarApi = fullCalendar.value.getApi()
        calendarApi.removeAllEvents()
        events.value.forEach((evt) => {
          calendarApi.addEvent(evt)
        })
      }
    }

    // 登録作品の取得
    const fetchRegisteredWorks = async () => {
      try {
        const response = await axios.get('/user/registered-works')
        registeredWorks.value = response.data
      } catch (error) {
        console.error('登録作品の取得に失敗しました。', error)
      } finally {
        isLoading.value = false
        await nextTick()
        initDraggable()
      }
    }

    const getPosterUrl = (path) =>
      path ? `https://image.tmdb.org/t/p/w200${path}` : '/placeholder-image.jpg'

    // ドラッグ可能にする
    const initDraggable = () => {
      const containerEl = document.querySelector('.works-list')
      if (containerEl) {
        new Draggable(containerEl, {
          itemSelector: '.work-item',
          eventData: (eventEl) => {
            return JSON.parse(eventEl.getAttribute('data-event'))
          },
        })
      }
    }

    // ドラッグ要素からイベントデータを生成
    const getEventData = (work) => {
      return {
        title: work.title || work.name,
        extendedProps: {
          work_id: work.id,
          poster_path: work.poster_path,
          overview: work.overview,
        },
      }
    }

    // ドロップでイベント受け取り
    const handleEventReceive = async (info) => {
      const eventData = info.event
      const date = eventData.startStr

      // 同じ日に同じ作品がないかチェック
      const isDuplicate = events.value.some(
        (ev) => ev.title === eventData.title && ev.start === date
      )
      if (isDuplicate) {
        alert('同じ日に同じ作品は登録できません。')
        info.revert()
        return
      }

      // サーバーに保存
      try {
        const response = await axios.post('/schedule', {
          title: eventData.title,
          date,
          work_id: eventData.extendedProps.work_id,
        })
        const savedEvent = response.data.schedule

        // カレンダー上の仮イベントを消す
        info.event.remove()

        // スケジュール配列に追加
        events.value.push(savedEvent)

        // カレンダーにも反映
        const calendarApi = fullCalendar.value.getApi()
        calendarApi.addEvent(savedEvent)
      } catch (error) {
        console.error('イベント保存エラー:', error)
        info.revert()
      }
    }

    // イベントをクリックしたとき
    const handleEventClick = (info) => {
      selectedEvent.value = info.event
      showEventModal.value = true
    }

    // イベントモーダルを閉じる
    const closeEventModal = () => {
      showEventModal.value = false
      selectedEvent.value = null
    }

    // イベント削除
    const deleteEvent = async () => {
      if (confirm('このイベントを削除しますか？')) {
        try {
          await axios.delete(
            `/schedule/${selectedEvent.value.extendedProps.id}`
          )
          // events配列からも削除
          events.value = events.value.filter(
            (ev) =>
              ev.extendedProps.id !== selectedEvent.value.extendedProps.id
          )
          // カレンダーから削除
          selectedEvent.value.remove()
          closeEventModal()
        } catch (error) {
          console.error('イベント削除エラー:', error)
        }
      }
    }

    // 日付クリックでモーダルを開く
    const openDateModal = (info) => {
      selectedDate.value = info.dateStr
      showDateModal.value = true
    }

    // モーダルを閉じる
    const closeDateModal = () => {
      showDateModal.value = false
    }

    // モーダルからイベント追加
    const addEventToCalendar = async (work) => {
      const isDuplicate = events.value.some(
        (ev) => ev.title === work.title && ev.start === selectedDate.value
      )
      if (isDuplicate) {
        alert('同じ日に同じ作品は登録できません。')
        return
      }
      try {
        const response = await axios.post('/schedule', {
          title: work.title || work.name,
          date: selectedDate.value,
          work_id: work.id,
        })
        const savedEvent = response.data.schedule
        events.value.push(savedEvent)
        const calendarApi = fullCalendar.value.getApi()
        calendarApi.addEvent(savedEvent)
        closeDateModal()
      } catch (error) {
        console.error('イベント保存エラー:', error)
      }
    }

    onMounted(() => {
      fetchEvents()
      fetchRegisteredWorks()
    })

    const calendarOptions = {
      plugins: [dayGridPlugin, interactionPlugin],
      initialView: 'dayGridMonth',
      droppable: true,
      eventReceive: handleEventReceive,
      eventClick: handleEventClick,
      dateClick: openDateModal,
      height: 'auto',
      contentHeight: 500,
    }

    return {
      fullCalendar,
      events,
      registeredWorks,
      isLoading,
      showEventModal,
      selectedEvent,
      showDateModal,
      selectedDate,
      calendarOptions,
      fetchEvents,
      fetchRegisteredWorks,
      getPosterUrl,
      initDraggable,
      getEventData,
      handleEventReceive,
      handleEventClick,
      closeEventModal,
      deleteEvent,
      openDateModal,
      closeDateModal,
      addEventToCalendar,
    }
  },
}
</script>

<style scoped>
.schedule-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 20px;
}

.calendar-section {
  flex: 1 1 600px;
  max-width: 800px;
}

.works-section {
  flex: 1 1 300px;
  max-width: 400px;
}

.works-list {
  list-style: none;
  padding: 0;
}

.work-item {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
  cursor: move; /* ドラッグ可能 */
}

.poster-img {
  width: 50px;
  height: auto;
  margin-right: 10px;
}

.loading {
  font-size: 1em;
  color: #333;
}

/* モーダル */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
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
  max-width: 500px;
  max-height: 80%;
  overflow-y: auto;
}

.modal-overview {
  margin-top: 15px;
  font-size: 0.9em;
  color: #333;
}

.modal-buttons {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}

.delete-button {
  margin-right: 10px;
  padding: 0.5em 1em;
  font-size: 0.9em;
  color: white;
  background-color: #dc3545;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.delete-button:hover {
  background-color: #c82333;
}

.close-modal-button {
  padding: 0.5em 1em;
  font-size: 0.9em;
  color: #333;
  background-color: #eee;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.close-modal-button:hover {
  background-color: #ddd;
}

.modal-works-list {
  list-style: none;
  padding: 0;
}

.modal-work-item {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.modal-work-item p {
  flex: 1;
  margin: 0;
}

.select-button {
  margin-left: 10px;
  padding: 0.5em 1em;
  font-size: 0.9em;
  color: white;
  background-color: #28a745;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.select-button:hover {
  background-color: #218838;
}
</style>
