<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <header class="header header_main">
                    <div class="header__inner">
                        <a href="/" class="header__logo">
                            <img
                                src="https://devskills.foncode.ru/img/logo.png"
                            />
                        </a>
                        <nav>
                            <a href="#">Банк знаний</a>
                            <a href="#">Каталог</a>
                            <a href="#">Для учителей</a>
                            <a href="#">Стажировки</a>
                            <a href="/news">Новости</a>
                            <a href="/contacts">Контакты</a>
                            <a href="/about">О нас</a>
                        </nav>
                        <div class="header__lk">
                            <a
                                href="https://devskills.foncode.ru/cabinet/profile"
                                class="header__lk-img"
                            >
                                <img
                                    src="https://devskills.foncode.ru/img/nofotolk.png"
                                />
                            </a>
                            <div class="header__lk-name">
                                <div class="personal-area personal-area_active">
                                    <div class="personal-area__inner">
                                        <!-- Показываем, если пользователь вошел -->
                                        <div v-if="user" class="header__lk">
                                            <a
                                                href="/profile"
                                                class="header__lk-img"
                                            >
                                                <img
                                                    src="https://devskills.foncode.ru/img/nofotolk.png"
                                                    width="40"
                                                    height="40"
                                                />
                                            </a>
                                            <div class="header__lk-name">
                                                <a
                                                    class="personal-area__username"
                                                    >{{ user.name }}</a
                                                >
                                                <a
                                                    href="#"
                                                    @click="logout"
                                                    class="personal-area__button"
                                                    >Выйти</a
                                                >
                                            </div>
                                        </div>
                                        <!-- Показываем, если НЕ вошел -->
                                        <div v-else>
                                            <a
                                                href="/login"
                                                class="personal-area__username"
                                                >Войти</a
                                            >
                                            <a
                                                href="/register"
                                                class="personal-area__username"
                                                >Регистрация</a
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="menu-btn">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </header>

                <div class="b-popup_enter">
                    <div class="b-popup__content">
                        <div class="b-popup__title">
                            Проверьте свой адрес электронной почты
                        </div>
                        <div class="b-popup__block"></div>
                        <div class="b-popup__block">
                            Прежде чем продолжить, проверьте свою электронную
                            почту на наличие ссылки для подтверждения. Если вы
                            не получили письмо, перейдите по ссылке
                        </div>
                        <div class="b-popup__block">
                            <div class="b-popup__block-right">
                                <button class="b-popup__input" @click="resendLink">Ссылка</button>
                            </div>
                        </div>
                        <div v-if="statusMessage" class="status-message">
                            {{ statusMessage }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import axios from 'axios';

export default {
  data() {
    return {
      // Достаем CSRF-токен из meta
      csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      userEmail: '',
      statusMessage: ''
    };
  },
  mounted() {
    // Пример: берем email из localStorage
    const pendingUser = localStorage.getItem("pendingUser");
    console.log("localstorage", pendingUser);
    if (pendingUser) {
      const parsed = JSON.parse(pendingUser);
      this.userEmail = parsed.email || "";
      console.log("Проверка userEmail:", this.userEmail);
    }
  },
  methods: {
    async resendLink() {
      try {
        console.log("Повторная отправка письма:", this.userEmail);
        console.log("CSRF-токен:", this.csrfToken);

        // Делаем POST-запрос на /resend-link
        const response = await axios.post('/resend-link', {
          user_email: this.userEmail
        }, {
          headers: {
            'X-CSRF-TOKEN': this.csrfToken
          }
        });

        console.log('Ответ сервера:', response.data);
        // Если сервер возвращает JSON с каким-то сообщением, покажем его
        if (response.data.status) {
          this.statusMessage = response.data.status;
        } else {
          this.statusMessage = 'Ссылка отправлена повторно!';
        }

      } catch (error) {
        console.error('Ошибка при повторной отправке:', error);
        this.statusMessage = 'Произошла ошибка при отправке. Попробуйте позже.';
      }
    }
  }
};
</script>
<style scoped>
.b-popup__input{
    border-radius: 18px;
    margin: 0 auto;
    max-width: 340px;
    width: 100%;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #fff;
    border: 0;
    box-sizing: border-box;
    text-transform: uppercase;
    background: #575adf;
    transition: .3s;
    cursor: pointer;
    font-size: 20px;
}
</style>