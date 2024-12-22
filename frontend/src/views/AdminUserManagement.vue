<template>
  <div class="admin-user-management">
    <h2>ユーザー管理</h2>
    
    <!-- ユーザー検索セクション -->
    <div class="search-section">
      <input
        type="text"
        v-model="searchUsername"
        placeholder="ユーザー名で検索"
        class="search-input"
      />
      <input
        type="text"
        v-model="searchEmail"
        placeholder="メールアドレスで検索"
        class="search-input"
      />
      <button @click="searchUsers" class="search-button">検索</button>
      <button @click="resetSearch" class="reset-button">リセット</button>
    </div>
    
    <div v-if="loading" class="loading">
      <p>ユーザー一覧を読み込み中...</p>
    </div>
    <div v-else>
      <div v-if="users.length === 0">登録されているユーザーはありません。</div>
      <table v-else class="user-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>ユーザー名</th>
            <th>メールアドレス</th>
            <th>管理者</th>
            <th>登録日</th>
            <th>操作</th>
            <th>レビュー管理</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.id }}</td>
            <td>{{ user.username }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.is_admin ? 'はい' : 'いいえ' }}</td>
            <td>{{ formatDate(user.created_at) }}</td>
            <td>
              <button @click="deleteUser(user.id)" class="delete-button">削除</button>
            </td>
            <td>
              <router-link :to="{ name: 'AdminUserReviews', params: { id: user.id } }" class="review-link">レビュー管理</router-link>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
    </div>
    
    <!-- ユーザー削除確認モーダル -->
    <div v-if="showDeleteModal" class="modal">
      <div class="modal-content">
        <span class="close" @click="closeDeleteModal">&times;</span>
        <h3>ユーザー削除確認</h3>
        <p>本当にこのユーザーを削除しますか？</p>
        <button @click="confirmDelete" class="confirm-button">はい</button>
        <button @click="closeDeleteModal" class="cancel-button">いいえ</button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import axios from '../axios'

export default {
  name: 'AdminUserManagement',
  setup() {
    const users = ref([])
    const loading = ref(true)
    const errorMessage = ref('')
    
    // 検索用のリファレンス
    const searchUsername = ref('')
    const searchEmail = ref('')
    
    // ユーザー削除用
    const showDeleteModal = ref(false)
    const userToDelete = ref(null)

    const loadUsers = async () => {
      loading.value = true
      try {
        const response = await axios.get('/admin/users')
        users.value = response.data
        errorMessage.value = ''
      } catch (error) {
        console.error('ユーザー一覧の取得に失敗しました。', error)
        errorMessage.value = 'ユーザー一覧の取得に失敗しました。'
      } finally {
        loading.value = false
      }
    }

    const searchUsers = async () => {
      loading.value = true
      try {
        const params = {}
        if (searchUsername.value) params.username = searchUsername.value
        if (searchEmail.value) params.email = searchEmail.value

        const response = await axios.get('/admin/users', { params })
        users.value = response.data
        errorMessage.value = ''
      } catch (error) {
        console.error('ユーザー検索に失敗しました。', error)
        errorMessage.value = 'ユーザー検索に失敗しました。'
      } finally {
        loading.value = false
      }
    }

    const resetSearch = async () => {
      searchUsername.value = ''
      searchEmail.value = ''
      await loadUsers()
    }

    const deleteUser = (userId) => {
      userToDelete.value = userId
      showDeleteModal.value = true
    }

    const confirmDelete = async () => {
      try {
        await axios.delete(`/admin/users/${userToDelete.value}`)
        users.value = users.value.filter(user => user.id !== userToDelete.value)
        errorMessage.value = ''
        alert('ユーザーが削除されました。')
      } catch (error) {
        console.error('ユーザーの削除に失敗しました。', error)
        errorMessage.value = 'ユーザーの削除に失敗しました。'
      } finally {
        showDeleteModal.value = false
        userToDelete.value = null
      }
    }

    const closeDeleteModal = () => {
      showDeleteModal.value = false
      userToDelete.value = null
    }

    const formatDate = (dateStr) => {
      if (!dateStr) return ''
      const date = new Date(dateStr)
      return `${date.getFullYear()}-${(date.getMonth()+1).toString().padStart(2,'0')}-${date.getDate().toString().padStart(2,'0')}`
    }

    onMounted(loadUsers)

    return {
      users,
      loading,
      errorMessage,
      searchUsername,
      searchEmail,
      searchUsers,
      resetSearch,
      deleteUser,
      showDeleteModal,
      confirmDelete,
      closeDeleteModal,
      formatDate,
    }
  }
}
</script>

<style scoped>
.admin-user-management {
  padding: 2em;
}

.loading {
  text-align: center;
  font-size: 1.2em;
  color: #555;
}

.search-section {
  margin-bottom: 1em;
}

.search-input {
  padding: 0.5em;
  margin-right: 0.5em;
  border: 1px solid #ccc;
  border-radius: 3px;
}

.search-button,
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
  margin-left: 0.5em;
}

.search-button:hover {
  background-color: #0069d9;
}

.reset-button:hover {
  background-color: #5a6268;
}

.user-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1em;
}

.user-table th, .user-table td {
  border: 1px solid #ddd;
  padding: 0.5em;
  text-align: left;
}

.user-table th {
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

.review-link {
  color: #007bff;
  text-decoration: none;
}

.review-link:hover {
  text-decoration: underline;
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
