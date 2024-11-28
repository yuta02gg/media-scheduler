<template>
  <div class="register-container">
    <h1>ユーザー登録</h1>
    <form @submit.prevent="register" class="register-form">
      <div class="input-group">
        <input v-model="username" type="text" placeholder="ユーザー名" required class="form-input"/>
      </div>
      <div class="input-group">
        <input v-model="email" type="email" placeholder="メールアドレス" required class="form-input"/>
      </div>
      <div class="input-group password-group">
        <input :type="showPassword ? 'text' : 'password'" v-model="password" placeholder="パスワード" required class="form-input" />
        <button type="button" @click="toggleShowPassword" class="toggle-button">
          {{ showPassword ? '非表示' : '表示' }}
        </button>
      </div>
      <p v-if="password.length > 0 && password.length < 6" class="error">パスワードは6文字以上必要です。</p>
      
      <div class="input-group password-group">
        <input :type="showPassword ? 'text' : 'password'" v-model="password_confirmation" placeholder="パスワード確認" required class="form-input" />
      </div>
      <p v-if="password !== password_confirmation && password_confirmation.length > 0" class="error">パスワードが一致しません。</p>

      <button type="submit" :disabled="isSubmitDisabled" class="submit-button">登録</button>
    </form>
    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'

export default {
  name: 'UserRegister',
  setup() {
    const username = ref('')
    const email = ref('')
    const password = ref('')
    const password_confirmation = ref('')
    const showPassword = ref(false)
    const errorMessage = ref('')

    const store = useStore()
    const router = useRouter()

    const toggleShowPassword = () => {
      showPassword.value = !showPassword.value
    }

    const isSubmitDisabled = computed(() => {
      return password.value.length < 6 || password.value !== password_confirmation.value
    })

    const register = async () => {
      if (isSubmitDisabled.value) return
      try {
        await store.dispatch('register', {
          username: username.value,
          email: email.value,
          password: password.value,
          password_confirmation: password_confirmation.value,
        })
        router.push('/mypage')
      } catch (error) {
        errorMessage.value = '登録に失敗しました。入力内容を確認してください。'
        console.error(error)
      }
    }

    return {
      username,
      email,
      password,
      password_confirmation,
      showPassword,
      errorMessage,
      toggleShowPassword,
      isSubmitDisabled,
      register,
    }
  }
}
</script>

<style scoped>
.register-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #ffffff;
}

h1 {
  font-size: 2.5em;
  margin-bottom: 1em;
  color: #333333;
  animation: fadeIn 1.5s ease;
  font-family: 'Montserrat', sans-serif;
}

.register-form {
  width: 100%;
  max-width: 400px;
  padding: 2em;
  background-color: #f9f9f9;
  border-radius: 8px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  animation: fadeIn 2s ease;
}

.input-group {
  margin-bottom: 1em;
}

.form-input {
  width: 100%;
  padding: 0.8em;
  font-size: 1em;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.password-group {
  display: flex;
  align-items: center;
}

.toggle-button {
  margin-left: 10px;
  padding: 0.4em 0.8em;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.toggle-button:hover {
  background-color: #0056b3;
}

.submit-button {
  width: 100%;
  padding: 1em;
  font-size: 1em;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.submit-button:disabled {
  background-color: #aaa;
  cursor: not-allowed;
}

.submit-button:not(:disabled):hover {
  background-color: #218838;
}

.error {
  color: red;
  font-size: 0.9em;
  margin-top: 0.5em;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
