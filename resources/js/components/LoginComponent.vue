<template>
  <div>
    <div class="maincontainer">
      <div class="container">
        <div class="b-popup_enter">
          <div class="b-popup__content">
            <!-- Форма входа -->
            <div v-if="currentStep === 1">
              <div class="b-popup__title">Войти в личный кабинет</div>
              <form @submit.prevent="handleLogin">
                <input
                  type="hidden"
                  name="_token"
                  :value="csrfToken"
                  autocomplete="off"
                />
                <div class="b-popup__block">
                  <p :class="{ 'input-error--p': errors.login }">E-mail</p>
                  <div class="b-popup__block-right">
                    <input
                      id="login"
                      type="text"
                      v-model="login"
                      placeholder="E-mail"
                      autofocus
                      :class="{ 'input-error': errors.login }"
                      @input="validateLogin"
                    />
                    <span v-if="errors.login" class="error-text">
                      {{ errors.login }}
                    </span>
                  </div>
                </div>
                <div class="b-popup__block">
                  <p :class="{ 'input-error--p': errors.password }">Пароль</p>
                  <div class="b-popup__block-right">
                    <input
                      type="password"
                      v-model="password"
                      placeholder="Введите пароль"
                      autocomplete="current-password"
                      :class="{ 'input-error': errors.password }"
                      @input="validateLoginPassword"
                    />
                    <span v-if="errors.password" class="error-text">
                      {{ errors.password }}
                    </span>
                  </div>
                </div>
                <div class="b-popup__block">
                  <div class="b-popup__block-right">
                    <input
                      type="submit"
                      name="submit"
                      class="button"
                      value="Войти"
                    />
                  </div>
                </div>
                <div class="b-popup__block" style="justify-content: right;">
                      <div class="b-popup__link-social" style="margin: 0px;">
                      <a href="/reset-password" class="b-popup__forget">Забыли пароль?</a>
                      </div>
                  </div>
                <!-- Общее сообщение об ошибке (если есть) -->
                <div v-if="errorMessage" class="error">
                  {{ errorMessage }}
                </div>
              </form>
            </div>
            <!-- Форма ввода кода и успешный вход можно оставить без изменений -->
            <div v-if="currentStep === 2">
              <div class="b-popup__title">Введите код подтверждения</div>
              <form @submit.prevent="handleVerifyCode">
                <div class="b-popup__block">
                  <p>Код</p>
                  <div class="b-popup__block-right">
                    <input
                      id="code"
                      type="text"
                      v-model="code"
                      required
                      maxlength="6"
                    />
                  </div>
                </div>
                <div class="b-popup__block">
                  <div class="b-popup__block-right">
                    <input
                      type="submit"
                      class="button"
                      value="Подтвердить"
                    />
                  </div>
                </div>
                <div v-if="errorMessage" class="error">
                  {{ errorMessage }}
                </div>
              </form>
            </div>
            <div v-if="currentStep === 3">
              <div class="b-popup__title">Добро пожаловать!</div>
              <p>Вы успешно вошли в систему.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const currentStep = ref(1) // 1 - вход, 2 - подтверждение кода, 3 - успешный вход
const login = ref('')
const password = ref('')
const code = ref('')
const errorMessage = ref('')
const csrfToken = ref('')

// Объект ошибок для полей
const errors = ref({
  login: null,
  password: null
})

/**
 * Безопасное кодирование строки в Base64 для любых символов (UTF-8).
 * Стандартный btoa() даёт ошибку, если есть символы вне Latin1 диапазона.
 */
function toBase64(str) {
  return btoa(unescape(encodeURIComponent(str)));
}

// Проверка поля E-mail
const validateLogin = () => {
  if (!login.value) {
    errors.value.login = "Поле E-mail обязательно."
  } else if (!/\S+@\S+\.\S+/.test(login.value)) {
    errors.value.login = "Введите корректный E-mail."
  } else {
    errors.value.login = null
  }
}

// Проверка поля Пароль
const validateLoginPassword = () => {
  if (!password.value) {
    errors.value.password = "Поле Пароль обязательно."
  } else {
    errors.value.password = null
  }
}

// Обработчик входа
const handleLogin = async () => {
  errorMessage.value = ''
  // Валидация полей
  validateLogin()
  validateLoginPassword()
  
  // Если есть ошибки — не отправляем запрос
  if (errors.value.login || errors.value.password) {
    console.log("Ошибка валидации:", errors.value)
    return
  }
  
  try {
    const response = await axios.post(
      '/login',
      {
        login: login.value,
        password: password.value
      },
      { headers: { 'X-CSRF-TOKEN': csrfToken.value } }
    )

    if (response.data.success) {
      console.log(`✅ Код отправлен на почту: ${login.value}`)
      currentStep.value = 2
    }
  } catch (error) {
    console.error('❌ Ошибка входа:', error.response?.data || error)
    errorMessage.value = error.response?.data?.message || 'Ошибка входа. Проверьте данные.'
  }
}

// Обработчик подтверждения кода
const handleVerifyCode = async () => {
  errorMessage.value = ''
  try {
    const response = await axios.post('/verify-code', { code: code.value })
    if (response.data.success) {
      const { password, created_at, updated_at, ...userData } = response.data.user
      // Сохраняем пользователя в localStorage
      localStorage.setItem('user', JSON.stringify(userData))
      currentStep.value = 3
      
      // Кодируем userData в Base64, безопасно для не-латинских символов
      const base64User = encodeURIComponent(toBase64(JSON.stringify(userData)))
      
      // Перенаправляем в зависимости от роли
      if (userData.role === 4) {
        window.location.href = `/student?verifiedUser=${base64User}`
      } else if(userData.role === 3){
      window.location.href = `/admin?verifiedUser=${base64User}`
      } else if(userData.role === 2){
        window.location.href = `/teacher?verifiedUser=${base64User}`
      }
      else if(userData.role === 1) {
        window.location.href = `/?verifiedUser=${base64User}`
      }
        
      
    }
  } catch (error) {
    errorMessage.value = 'Неверный код. Попробуйте снова.'
    console.error('❌ Ошибка подтверждения кода:', error)
  }
}

// Выход
const logout = () => {
  localStorage.removeItem('user')
  window.location.href = '/'
}

onMounted(() => {
  // Загружаем CSRF-токен из meta-тега
  const token = document.querySelector('meta[name="csrf-token"]')
  if (token) {
    csrfToken.value = token.content
  }
})

// Следим за изменением полей и валидируем
watch(login, () => {
  validateLogin()
})

watch(password, () => {
  validateLoginPassword()
})
</script>

<style scoped>
.input-error {
  border: 1px solid red !important;
}

.input-error::placeholder {
  color: rgba(255, 0, 0, 0.466);
}

.input-error--p {
  color: red !important;
}

.error-text {
  color: red;
  font-size: 12px;
}

.error {
  color: red;
  margin-top: 10px;
}
</style>




