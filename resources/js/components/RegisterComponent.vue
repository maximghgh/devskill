<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <div class="b-popup_register">
                    <div class="b-popup__content">
                        <div class="b-popup__title">Регистрация</div>
                        <form @submit.prevent="register">
                            <input
                                type="hidden"
                                name="_token"
                                :value="csrfToken"
                            />

                            <div class="b-popup__block">
                                <p :class="{ 'input-error--p': errors.name }">
                                    ФИО
                                </p>
                                <div class="b-popup__block-right">
                                    <input
                                        id="name"
                                        type="text"
                                        name="name"
                                        v-model="user.name"
                                        autocomplete="name"
                                        placeholder="ФИО"
                                        @input="validateName"  
                                        :class="{ 'input-error': errors.name }"
                                    />
                                    <span v-if="errors.name" class="error-text">
                                        {{ errors.name }}
                                    </span>
                                </div>
                            </div>

                            <div class="b-popup__block">
                                <p :class="{ 'input-error--p': errors.email }">
                                    E-mail
                                </p>
                                <div class="b-popup__block-right">
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        v-model="user.email"
                                        autocomplete="email"
                                        placeholder="E-mail"
                                        @input="validateEmail"  
                                        :class="{ 'input-error': errors.email }"
                                    />
                                    <span v-if="errors.email" class="error-text">
                                        {{ errors.email }}
                                    </span>
                                </div>
                            </div>

                            <div class="b-popup__block">
                                <p :class="{ 'input-error--p': errors.password }">
                                    Пароль
                                </p>
                                <div class="b-popup__block-right">
                                    <input
                                        id="password"
                                        type="password"
                                        name="password"
                                        v-model="user.password"
                                        autocomplete="new-password"
                                        placeholder="Пароль не менее пяти символов"
                                        @input="validatePassword"  
                                        :class="{ 'input-error': errors.password }"
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
                                        class="button"
                                        value="Зарегистрироваться"
                                    />
                                </div>
                            </div>
                            <p v-if="message">{{ message }}</p>
                        </form>

                        <div class="b-popup__link-social">
                            <a
                                href="https://devskills.foncode.ru/login"
                                title="Вход"
                            >Вход</a>
                            <a
                                href="https://devskills.foncode.ru/social-auth/vkontakte"
                                title="Vkontakte"
                            >По ВКонтакте</a>
                        </div>
                        <div style="margin-top: 20px; font-size: 14px">
                            <input
                                type="checkbox"
                                checked
                                id="checkbox_rules"
                            />
                            Нажимая на кнопку, я даю
                            <a
                                style="font-size: 14px"
                                target="_blank"
                                href="https://foncode.ru/docs/СОГЛАШЕНИЕ_НА_ОБРАБОТКУ_ПЕРСОНАЛЬНЫХ_ДАННЫХ.pdf"
                            >согласие на обработку персональных данных</a>
                            и подтверждаю, что ознакомлен с условиями
                            <a
                                style="font-size: 14px"
                                target="_blank"
                                href="https://foncode.ru/docs/Политика%20конфиденциальности.pdf"
                            >политики конфиденциальности</a>.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from "vue";
import axios from "axios";

export default {
    setup() {
        const user = ref({ name: "", email: "", password: "" });
        const message = ref("");
        const errors = ref({ name: null, email: null, password: null });

        // ============== Методы валидации полей ==================
        const validateName = () => {
            if (!user.value.name) {
                errors.value.name = "Поле ФИО обязательно.";
            } else {
                errors.value.name = null;
            }
        };

        const validateEmail = () => {
            if (!user.value.email) {
                errors.value.email = "Поле E-mail обязательно.";
            } else if (!/\S+@\S+\.\S+/.test(user.value.email)) {
                errors.value.email = "Введите корректный E-mail.";
            } else {
                errors.value.email = null;
            }
        };

        const validatePassword = () => {
            if (!user.value.password) {
                errors.value.password = "Поле Пароль обязательно.";
            } else if (user.value.password.length < 5) {
                errors.value.password = "Пароль должен содержать не менее 5 символов.";
            } else {
                errors.value.password = null;
            }
        };

        // Общая проверка перед отправкой формы
        const validateForm = () => {
            // Вызываем методы, чтобы убедиться, что все поля проверены
            validateName();
            validateEmail();
            validatePassword();

            return (
                !errors.value.name &&
                !errors.value.email &&
                !errors.value.password
            );
        };

        // Функция регистрации
        const register = async () => {
            if (!validateForm()) {
                console.log("Форма содержит ошибки:", errors.value);
                return;
            }

            console.log("Сохраняем данные в localStorage:", user.value);
            localStorage.setItem("pendingUser", JSON.stringify(user.value));

            try {
                console.log("📤 Отправляем данные:", user.value);
                const response = await axios.post("/api/register", {
                    email: user.value.email,
                    name: user.value.name,
                    password: user.value.password,
                });

                message.value = response.data.message;
                console.log("Ответ от сервера:", response.data);

                if (response.data.success) {
                    console.log(
                        `✅ Письмо с подтверждением отправлено на: ${user.value.email}`
                    );
                    if (response.data.redirect) {
                        setTimeout(() => {
                            window.location.href = response.data.redirect;
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

        // Ваша вспомогательная функция логирования (по желанию)
        const logInput = () => {
            console.log("Текущее состояние формы:", user.value);
        };

        return {
            user,
            message,
            errors,
            register,
            logInput,
            validateName,
            validateEmail,
            validatePassword
        };
    },
};
</script>

<style scoped>
.input-error {
    border: 1px solid red !important;
}

.input-error::placeholder {
    color: rgba(255, 0, 0, 0.466);
}

/* Класс для изменения цвета текста меток */
.input-error--p {
    color: red !important;
}

.error-text {
    color: red;
    font-size: 12px;
}
</style>
