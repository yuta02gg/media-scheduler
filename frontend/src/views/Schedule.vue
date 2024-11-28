<template>
  <div class="schedule-container">
    <div class="calendar-section">
      <h1>スケジュール管理</h1>
      <FullCalendar ref="fullCalendar" :options="calendarOptions" />
    </div>
    <div class="works-section">
      <h2>登録作品</h2>
      <div v-if="isLoading" class="loading">読み込み中...</div>
      <div v-else>
        <p v-if="registeredWorks.length === 0">登録された作品はありません。</p>
        <ul class="works-list">
          <li
            v-for="work in registeredWorks"
            :key="work.id"
            class="work-item"
            :data-event="JSON.stringify(getEventData(work))"
          >
            <img :src="getPosterUrl(work.poster_path)" :alt="work.title" class="poster-img" />
            <p>{{ work.title }}</p>
          </li>
        </ul>
      </div>
    </div>

    <!-- イベント詳細モーダル -->
    <div v-if="showEventModal" class="modal-overlay">
      <div class="modal-content">
        <h3>{{ selectedEvent.title }}</h3>
        <p class="modal-overview">{{ selectedEvent.extendedProps.overview }}</p>
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
          <li v-for="work in registeredWorks" :key="work.id" class="modal-work-item">
            <p>{{ work.title || work.name }}</p>
            <button @click="addEventToCalendar(work)" class="select-button">選択する</button>
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

    const fetchEvents = async () => {
      try {
        const response = await axios.get('/schedule')
        events.value = response.data
        // カレンダーにイベントを設定
        updateCalendarEvents()
      } catch (error) {
        console.error(error)
      }
    }

    const updateCalendarEvents = () => {
      if (fullCalendar.value) {
        const calendarApi = fullCalendar.value.getApi()
        calendarApi.removeAllEvents()
        events.value.forEach(event => {
          calendarApi.addEvent(event)
        })
      }
    }

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

    const getPosterUrl = (path) => {
      return path ? `https://image.tmdb.org/t/p/w200${path}` : '/placeholder-image.jpg'
    }

    const initDraggable = () => {
      const containerEl = document.querySelector('.works-list')
      if (containerEl) {
        new Draggable(containerEl, {
          itemSelector: '.work-item',
          eventData: function(eventEl) {
            return JSON.parse(eventEl.getAttribute('data-event'))
          },
        })
      }
    }

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

    const handleEventReceive = async (info) => {
      const eventData = info.event
      const date = eventData.startStr

      // 同じ日に同じ作品が登録されていないかチェック
      const isDuplicate = events.value.some(
        (event) =>
          event.title === eventData.title && event.start === date
      )

      if (isDuplicate) {
        alert('同じ日に同じ作品を登録することはできません。')
        info.revert()
        return
      }

      try {
        // イベントをサーバーに保存
        const response = await axios.post('/schedule', {
          title: eventData.title,
          date: date,
          work_id: eventData.extendedProps.work_id,
        })

        // サーバーから返されたイベントデータを取得
        const savedEvent = response.data.schedule

        // カレンダー上の一時的なイベントを削除
        info.event.remove()

        // `events` 配列に新しいイベントを追加
        events.value.push(savedEvent)

        // カレンダーにイベントを追加
        const calendarApi = fullCalendar.value.getApi()
        calendarApi.addEvent(savedEvent)
      } catch (error) {
        console.error('イベントの保存に失敗しました。', error)
        info.revert()
      }
    }

    const handleEventClick = (info) => {
      selectedEvent.value = info.event
      showEventModal.value = true
    }

    const closeEventModal = () => {
      showEventModal.value = false
      selectedEvent.value = null
    }

    const deleteEvent = async () => {
      if (confirm('このイベントを削除しますか？')) {
        try {
          await axios.delete(`/schedule/${selectedEvent.value.extendedProps.id}`)
          // `events` から削除
          events.value = events.value.filter(
            (event) => event.extendedProps.id !== selectedEvent.value.extendedProps.id
          )
          // カレンダーからイベントを削除
          selectedEvent.value.remove()
          closeEventModal()
        } catch (error) {
          console.error('イベントの削除に失敗しました。', error)
        }
      }
    }

    const openDateModal = (info) => {
      selectedDate.value = info.dateStr
      showDateModal.value = true
    }

    const closeDateModal = () => {
      showDateModal.value = false
    }

    const addEventToCalendar = async (work) => {
      // 同じ日に同じ作品が登録されていないかチェック
      const isDuplicate = events.value.some(
        (event) =>
          event.title === work.title && event.start === selectedDate.value
      )

      if (isDuplicate) {
        alert('同じ日に同じ作品を登録することはできません。')
        return
      }

      try {
        const response = await axios.post('/schedule', {
          title: work.title || work.name,
          date: selectedDate.value,
          work_id: work.id,
        })
        // サーバーから返されたイベントを取得
        const savedEvent = response.data.schedule

        // `events` 配列に新しいイベントを追加
        events.value.push(savedEvent)

        // カレンダーにイベントを追加
        const calendarApi = fullCalendar.value.getApi()
        calendarApi.addEvent(savedEvent)

        closeDateModal()
      } catch (error) {
        console.error('イベントの保存に失敗しました。', error)
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
      calendarOptions,
      registeredWorks,
      isLoading,
      getPosterUrl,
      showEventModal,
      selectedEvent,
      closeEventModal,
      deleteEvent,
      getEventData,
      showDateModal,
      selectedDate,
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
  cursor: move; /* ドラッグ可能であることを示す */
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
