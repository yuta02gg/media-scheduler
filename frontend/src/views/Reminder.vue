<template>
  <div class="reminder-settings">
    <h1>リマインダー設定</h1>
    <form @submit.prevent="setReminder" class="reminder-form">
      <label>
        リマインダーを設定する作品を選択:
        <select v-model="selectedWork" required>
          <option value="" disabled>作品を選択してください</option>
          <option v-for="work in works" :key="work.id" :value="work.id">
            {{ work.title || work.name }}
          </option>
        </select>
      </label>
      <label>
        リマインダー日時:
        <input type="datetime-local" v-model="reminderTime" required />
      </label>
      <button type="submit">設定</button>
    </form>
    <p v-if="message" class="message">{{ message }}</p>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import axios from '../axios'

export default {
  name: 'ReminderSettings',
  setup() {
    const works = ref([])
    const selectedWork = ref('')
    const reminderTime = ref('')
    const message = ref('')

    const fetchWorks = async () => {
      try {
        const response = await axios.get('/user/registered-works')
        works.value = response.data
      } catch (error) {
        console.error('作品の取得に失敗しました。', error)
      }
    }

    const setReminder = async () => {
      if (!selectedWork.value || !reminderTime.value) {
        message.value = '作品と日時を選択してください。'
        return
      }

      try {
        await axios.post('/reminders', {
          work_id: selectedWork.value,
          reminder_time: reminderTime.value,
        })
        message.value = 'リマインダーを設定しました。'
      } catch (error) {
        console.error('リマインダーの設定に失敗しました。', error)
        message.value = 'リマインダーの設定に失敗しました。'
      }
    }

    onMounted(() => {
      fetchWorks()
    })

    return {
      works,
      selectedWork,
      reminderTime,
      setReminder,
      message,
    }
  },
}
</script>

<style scoped>
.reminder-settings {
  padding: 2em;
}

.reminder-form {
  display: flex;
  flex-direction: column;
  max-width: 400px;
}

.reminder-form label {
  margin-bottom: 1em;
}

.reminder-form select,
.reminder-form input {
  padding: 0.5em;
  font-size: 1em;
  margin-top: 0.5em;
}

button {
  padding: 0.5em 1em;
  font-size: 1em;
  color: white;
  background-color: #28a745;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

button:hover {
  background-color: #218838;
}

.message {
  margin-top: 1em;
  color: #333;
}
</style>
