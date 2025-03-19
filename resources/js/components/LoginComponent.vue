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
                    <input type="hidden" name="_token" :value="csrfToken" autocomplete="off">
                    <div class="b-popup__block">
                        <p>E-mail</p>
                        <div class="b-popup__block-right">
                        <input id="login" type="text" v-model="login" required autofocus />
                        </div>
                    </div>
                    <div class="b-popup__block">
                        <p>Пароль</p>
                        <div class="b-popup__block-right">
                        <input type="password" v-model="password" required autocomplete="current-password" />
                        </div>
                    </div>
                    <div class="b-popup__block">
                        <div class="b-popup__block-right">
                        <input type="submit" name="submit" class="button" value="Войти">
                        </div>
                    </div>
                    <div class="b-popup__block" style="justify-content: right;">
                        <div class="b-popup__link-social" style="margin: 0px;">
                        <a href="/reset-password" class="b-popup__forget">Забыли пароль?</a>
                        <a href="/social-auth/vkontakte" title="Vkontakte">По ВКонтакте</a>
                        </div>
                    </div>
                    <div v-if="errorMessage" class="error">
                        {{ errorMessage }}
                    </div>
                    </form>
                </div>
                <!-- Форма ввода кода -->
                <div v-if="currentStep === 2">
                    <div class="b-popup__title">Введите код подтверждения</div>
                    <form @submit.prevent="handleVerifyCode">
                    <div class="b-popup__block">
                        <p>Код</p>
                        <div class="b-popup__block-right">
                        <input id="code" type="text" v-model="code" required maxlength="6" />
                        </div>
                    </div>
                    <div class="b-popup__block">
                        <div class="b-popup__block-right">
                        <input type="submit" class="button" value="Подтвердить">
                        </div>
                    </div>
                    <div v-if="errorMessage" class="error">
                        {{ errorMessage }}
                    </div>
                    </form>
                </div>
                <!-- Успешный вход -->
                <div v-if="currentStep === 3">
                    <div class="b-popup__title">Добро пожаловать!</div>
                    <p>Вы успешно вошли в систему.</p>
                </div>
                </div>
            </div>
        </div>  
        <footer>
            <div class="footer__inner">
                <div class="footer__row">
                    <div class="footer__block">API</div>
                    <div class="footer__block">
                        <div class="footer__logo">
                            <img src="https://devskills.foncode.ru/img/logo.png">
                        </div>
                    </div>
                </div>
                <div class="footer__row">© 2011-2023 гг. Сайт не является публичной офертой и носит информационный характер. Все материалы данного сайта являются объектами авторского права (в том числе дизайн). Запрещается копирование, распространение (в том числе путем копирования на другие сайты и ресурсы в Интернете) или любое иное использование информации и объектов без предварительного согласия правообладателя.</div>
            </div>
        </footer>
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
const user = ref(null) // Реактивное хранение пользователя

// Загружаем пользователя из localStorage при загрузке страницы
function loadUser() {
  const storedUser = localStorage.getItem('user')
  if (storedUser) {
    try {
      user.value = JSON.parse(storedUser)
      console.log('✅ Пользователь загружен:', user.value)
    } catch (error) {
      console.error('❌ Ошибка при разборе данных пользователя:', error)
    }
  }
}

// Следим за изменениями `user` и сохраняем его в `localStorage`
watch(user, (newValue) => {
  if (newValue) {
    localStorage.setItem('user', JSON.stringify(newValue))
  }
})

// Функция для безопасного кодирования в base64 (UTF-8)
function utf8ToBase64(str) {
  return btoa(unescape(encodeURIComponent(str)))
}

// Обработчик входа
const handleLogin = async () => {
  errorMessage.value = ''
  try {
    const response = await axios.post('/login', {
      login: login.value,
      password: password.value
    }, {
      headers: { 'X-CSRF-TOKEN': csrfToken.value }
    })

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
      user.value = userData // Обновляем `user` в реальном времени
      localStorage.setItem('user', JSON.stringify(userData)) // Сохраняем в `localStorage`

      const jsonString = JSON.stringify(userData)
      const base64String = utf8ToBase64(jsonString)
      const safeParam = encodeURIComponent(base64String)

      currentStep.value = 3 // Показываем "успешный вход"

      if (userData.role === 3) {
        window.location.href = `/admin?verifiedUser=${safeParam}`
      } else {
        window.location.href = `/?verifiedUser=${safeParam}`
      }
    }
  } catch (error) {
    errorMessage.value = 'Неверный код. Попробуйте снова.'
    console.error('❌ Ошибка подтверждения кода:', error)
  }
}

// Обработчик выхода
const logout = () => {
  localStorage.removeItem('user')
  user.value = null
  window.location.href = '/'
}

// Загружаем пользователя при монтировании компонента
onMounted(() => {
  loadUser()

  // Получаем CSRF-токен из meta-тега Laravel
  const token = document.querySelector('meta[name="csrf-token"]')
  if (token) {
    csrfToken.value = token.content
  }
})
</script>



