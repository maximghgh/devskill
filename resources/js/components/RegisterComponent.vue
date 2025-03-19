<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <header class="header header_main">
                    <div class="header__inner">
                        <a href="/" class="header__logo">
                            <img src="https://devskills.foncode.ru/img/logo.png" alt="Логотип">
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
                            <div class="personal-area personal-area_active">
                                <div class="personal-area__inner">
                                    <a href="/login" class="personal-area__button personal-area_enter">Войти</a>
                                    <a href="/register" class="personal-area__button personal-area_register">Регистрация</a>
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
                <div class="b-popup_register">
                    <div class="b-popup__content">
                        <div class="b-popup__title">Регистрация</div>
                        <form @submit.prevent="register">
                            <input type="hidden" name="_token" :value="csrfToken" />

                            <div class="b-popup__block">
                                <p>ФИО</p>
                                <div class="b-popup__block-right">
                                    <input id="name" type="text" name="name" v-model="user.name" required autocomplete="name" @input="logInput">
                                </div>
                            </div>
                            <div class="b-popup__block">
                                <p>E-mail</p>
                                <div class="b-popup__block-right">
                                    <input 
                                        id="email" 
                                        type="email" 
                                        name="email" 
                                        v-model="user.email" 
                                        required 
                                        autocomplete="email" 
                                        @input="validateEmail"
                                    >
                                    <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
                                </div>
                                </div>

                                <div class="b-popup__block">
                                <p>Пароль</p>
                                <div class="b-popup__block-right">
                                    <input 
                                        id="password" 
                                        type="password" 
                                        name="password" 
                                        v-model="user.password" 
                                        required 
                                        autocomplete="new-password" 
                                        @input="validatePassword"
                                    >
                                    <span class="error-text">{{ errors.password || "Пароль должен содержать не менее пяти символов." }}</span>
                                </div>
                            </div>
                            <div class="b-popup__block">
                                <div class="b-popup__block-right">
                                    <input type="submit" class="button" value="Зарегистрироваться">
                                </div>
                            </div>
                            <p v-if="message">{{ message }}</p>
                        </form>
                        <div class="b-popup__link-social">
                            <a href="https://devskills.foncode.ru/login" title="Вход">Вход</a>
                            <a href="https://devskills.foncode.ru/social-auth/vkontakte" title="Vkontakte">По ВКонтакте</a>
                        </div>
                        <div style="margin-top: 20px; font-size: 14px;">
                            <input type="checkbox" checked id="checkbox_rules">
                            Нажимая на кнопку, я даю 
                            <a style="font-size: 14px;" target="_blank" href="https://foncode.ru/docs/СОГЛАШЕНИЕ_НА_ОБРАБОТКУ_ПЕРСОНАЛЬНЫХ_ДАННЫХ.pdf">согласие на обработку персональных данных</a> 
                            и подтверждаю, что ознакомлен с условиями 
                            <a style="font-size: 14px;" target="_blank" href="https://foncode.ru/docs/Политика%20конфиденциальности.pdf">политики конфиденциальности</a>.
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
                                <img src="https://devskills.foncode.ru/img/logo.png" alt="Логотип">
                            </div>
                        </div>
                    </div>
                    <div class="footer__row">© 2011-2023 гг. Сайт не является публичной офертой и носит информационный характер. Все материалы данного сайта являются объектами авторского права (в том числе дизайн). Запрещается копирование, распространение (в том числе путем копирования на другие сайты и ресурсы в Интернете) или любое иное использование информации и объектов без предварительного согласия правообладателя.</div>
                </div>
            </footer>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';
import axios from 'axios';

export default {
    setup() {
        
        const user = ref({ name: '', email: '', password: '' });
        const message = ref('');
        const errors = ref({ name: null, email: null, password: null });

        // Функция логирования данных в консоль
        const logInput = () => {
            console.log("Текущее состояние формы:", user.value);
        };

        // Функция валидации перед отправкой формы
        const validateForm = () => {
            errors.value = { name: null, email: null, password: null };

            if (!user.value.name) {
                errors.value.name = "Поле ФИО обязательно.";
            }

            if (!user.value.email) {
                errors.value.email = "Поле E-mail обязательно.";
            } else if (!/\S+@\S+\.\S+/.test(user.value.email)) {
                errors.value.email = "Введите корректный E-mail.";
            }

            if (!user.value.password) {
                errors.value.password = "Поле Пароль обязательно.";
            } else if (user.value.password.length < 5) {
                errors.value.password = "Пароль должен содержать не менее 5 символов.";
            }

            return !errors.value.name && !errors.value.email && !errors.value.password;
        };

        // Функция регистрации
        const register = async () => {
            if (!validateForm()) {
                console.log("Форма содержит ошибки:", errors.value);
                return;
            }

            console.log("Сохраняем данные в localStorage:", user.value);
            localStorage.setItem("pendingUser", JSON.stringify(user.value)); // Сохраняем данные

            try {
                console.log("📤 Отправляем данные:", user.value);
                const response = await axios.post('/api/register', {
                    email: user.value.email,
                    name: user.value.name,
                    password: user.value.password
                });

                message.value = response.data.message;
                console.log("Ответ от сервера:", response.data);

                if (response.data.success) {
            console.log(`✅ Письмо с подтверждением отправлено на: ${user.value.email}`);
            
            
            if (response.data.redirect) {
                setTimeout(() => {
                    window.location.href = response.data.redirect; // 🔹 Перенаправляем пользователя
                }, 2000);
            }
                }
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    errors.value.email = "Пользователь с таким email уже существует.";
                } else {
                    message.value = "Ошибка регистрации. Попробуйте позже.";
                }
            }
        };

        return { user, message, errors, register, logInput };
    }
};
</script>



