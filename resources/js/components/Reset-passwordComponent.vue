<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <!-- Форма для ввода e-mail (отправка кода) -->
                <div class="b-popup_recovery" v-if="step === 'email'">
                    <div class="b-popup__content">
                        <div class="b-popup__title">Восстановление пароля</div>
                        <form @submit.prevent="sendResetCode">
                            <!-- ПРИМЕЧАНИЕ: CSRF-токен передаётся вместе с запросом -->
                            <!-- <input
                                type="hidden"
                                name="_token"
                                :value="csrfToken"
                                autocomplete="off"
                            /> -->
                            <div class="b-popup__block">
                                <p>E-mail</p>
                                <div class="b-popup__block-right">
                                    <input
                                        id="login"
                                        type="email"
                                        name="login"
                                        v-model="email"
                                        required
                                        autofocus
                                    />
                                    <span>Введите e-mail для восстановления пароля.</span>
                                </div>
                            </div>
                            <div v-if="emailError" class="error-message">
                                {{ emailError }}
                            </div>
                            <div class="b-popup__block">
                                <div class="b-popup__block-right">
                                    <input
                                        type="submit"
                                        name="submit"
                                        class="button"
                                        value="Восстановить"
                                    />
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Форма для ввода кода и нового пароля -->
                <div class="b-popup_recovery" v-else-if="step === 'reset'">
                    <div class="b-popup__content">
                        <div class="b-popup__title">Сброс пароля</div>
                        <form @submit.prevent="resetPassword">
                            <input
                                type="hidden"
                                name="_token"
                                :value="csrfToken"
                                autocomplete="off"
                            />
                            <div class="b-popup__block">
                                <p>E-mail</p>
                                <div class="b-popup__block-right">
                                    <!-- ПРИМЕЧАНИЕ: e-mail сохраняется из предыдущей формы и доступен для редактирования или только для просмотра -->
                                    <input
                                        id="emailReset"
                                        type="email"
                                        name="email"
                                        v-model="email"
                                        required
                                        readonly
                                    />
                                </div>
                            </div>
                            <div class="b-popup__block">
                                <p>Код из письма</p>
                                <div class="b-popup__block-right">
                                    <input
                                        id="code"
                                        type="text"
                                        name="code"
                                        v-model="code"
                                        required
                                    />
                                </div>
                            </div>
                            <div class="b-popup__block">
                                <p>Новый пароль</p>
                                <div class="b-popup__block-right">
                                    <input
                                        id="password"
                                        type="password"
                                        name="password"
                                        v-model="password"
                                        required
                                    />
                                </div>
                            </div>
                            <div class="b-popup__block">
                                <p>Подтверждение пароля</p>
                                <div class="b-popup__block-right">
                                    <input
                                        id="password_confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        v-model="passwordConfirmation"
                                        required
                                    />
                                </div>
                            </div>
                            <div class="b-popup__block">
                                <div class="b-popup__block-right">
                                    <input
                                        type="submit"
                                        name="submit"
                                        class="button"
                                        value="Сбросить пароль"
                                    />
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <footer>
                <div class="footer__inner">
                    <div class="footer__row">
                        <div class="footer__block">API</div>
                        <div class="footer__block">
                            <div class="footer__logo">
                                <img
                                    src="https://devskills.foncode.ru/img/logo.png"
                                    alt="Логотип"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="footer__row">
                        © 2011-2023 гг. Сайт не является публичной офертой и
                        носит информационный характер. Все материалы данного
                        сайта являются объектами авторского права (в том числе
                        дизайн). Запрещается копирование, распространение (в том
                        числе путем копирования на другие сайты и ресурсы в
                        Интернете) или любое иное использование информации и
                        объектов без предварительного согласия правообладателя.
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";

// Настраиваем CSRF-токен через заголовок (если требуется, можно добавить и withCredentials)
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
  axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
}


// Переменная для переключения шагов формы: 'email' для отправки кода, 'reset' для сброса пароля
const step = ref("email");

// Переменные для данных формы
const email = ref("");
const code = ref("");
const password = ref("");
const passwordConfirmation = ref("");

// Переменные для хранения ошибок
const emailError = ref("");
const resetError = ref("");

// Функция для отправки запроса на получение кода сброса пароля
async function sendResetCode() {
    // Сбрасываем предыдущую ошибку
    emailError.value = "";
    try {
        const response = await axios.post("/password/email", {
            login: email.value,
        });
        step.value = "reset";
        alert("Код для сброса пароля отправлен на ваш e-mail");
    } catch (error) {
        console.error("Ошибка отправки e-mail для сброса пароля", error);
        // Если есть ответ от сервера, выводим сообщение
        if (error.response && error.response.data && error.response.data.error) {
            emailError.value = error.response.data.error;
        } else {
            emailError.value = "Пользователь не найден";
        }
    }
}

// Функция для сброса пароля
async function resetPassword() {
    // Сбрасываем предыдущую ошибку
    resetError.value = "";
    try {
        const response = await axios.post("/password/reset", {
            email: email.value,
            code: code.value,
            password: password.value,
            password_confirmation: passwordConfirmation.value,
        });
        alert("Пароль успешно изменён");
        window.location.href = "/login";
    } catch (error) {
        console.error("Ошибка сброса пароля", error);
        if (error.response && error.response.data && error.response.data.error) {
            resetError.value = error.response.data.error;
        } else {
            resetError.value = "Ошибка сброса пароля";
        }
    }
}
</script>
