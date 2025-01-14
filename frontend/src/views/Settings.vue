<template>
  <div>
    <h1>設定</h1>
    <form @submit.prevent="updateSettings">
      <label>
        ユーザー名:
        <input v-model="username" required />
      </label>
      <label>
        メールアドレス:
        <input v-model="email" type="email" required />
      </label>

      <h2>パスワードの変更</h2>
      <label>
        新しいパスワード:
        <input v-model="newPassword" type="password" />
      </label>
      <label>
        新しいパスワード（確認）:
        <input v-model="newPasswordConfirmation" type="password" />
      </label>

      <button type="submit">更新</button>
    </form>

    <h2>アカウント削除</h2>
    <button @click="confirmDeleteAccount">アカウントを削除する</button>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import axios from '../axios'

export default {
  name: 'UserSettings',
  setup() {
    const username = ref('')
    const email = ref('')
    const newPassword = ref('')
    const newPasswordConfirmation = ref('')

    // ユーザー情報取得
    const fetchUser = async () => {
      try {
        const response = await axios.get('/user')
        username.value = response.data.username
        email.value = response.data.email
      } catch (error) {
        console.error(error)
        alert('ユーザー情報の取得に失敗しました')
      }
    }

    // 設定を更新
    const updateSettings = async () => {
      try {
        if (newPassword.value || newPasswordConfirmation.value) {
          if (newPassword.value !== newPasswordConfirmation.value) {
            alert('新しいパスワードが一致しません')
            return
          }
        }
        const data = {
          username: username.value,
          email: email.value,
        }
        if (newPassword.value) {
          data.new_password = newPassword.value
          data.new_password_confirmation = newPasswordConfirmation.value
        }
        await axios.put('/user', data)
        alert('設定を更新しました')

        // フィールドをクリア
        newPassword.value = ''
        newPasswordConfirmation.value = ''
      } catch (error) {
        console.error(error)
        if (error.response && error.response.data) {
          alert(error.response.data.message)
        } else {
          alert('設定の更新に失敗しました')
        }
      }
    }

    // アカウント削除確認
    const confirmDeleteAccount = () => {
      if (confirm('本当にアカウントを削除しますか？この操作は取り消せません。')) {
        deleteAccount()
      }
    }

    // アカウント削除
    const deleteAccount = async () => {
      try {
        await axios.delete('/user')
        alert('アカウントが削除されました')
        window.location.href = '/login'
      } catch (error) {
        console.error(error)
        alert('アカウントの削除に失敗しました')
      }
    }

    onMounted(fetchUser)

    return {
      username,
      email,
      newPassword,
      newPasswordConfirmation,
      updateSettings,
      confirmDeleteAccount,
    }
  },
}
</script>

<style scoped>
label {
  display: block;
  margin-bottom: 10px;
}

button {
  margin-top: 20px;
}

h2 {
  margin-top: 30px;
}
</style>
