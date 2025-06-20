<template>
    <div>
        <div class="maincontainer">
            <div class="container">
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
                                <button class="b-popup__input" @click="resendLink">Повторная ссылка</button>
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