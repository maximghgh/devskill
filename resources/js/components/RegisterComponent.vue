<template>
    <div>
        <div class="maincontainer">
            <div class="container">
                <div class="b-popup_register">
                    <div class="b-popup__content">
                        <div class="b-popup__title">Регистрация</div>

                        <!-- Блок выбора роли -->
                        <div class="role-switch">
                            <button
                                type="button"
                                class="role-btn"
                                :class="{ 'role-btn--active': currentRole === '1' }"
                                @click="setRole('1')"
                            >
                                Родитель
                            </button>
                            <button
                                type="button"
                                class="role-btn"
                                :class="{ 'role-btn--active': currentRole === '4' }"
                                @click="setRole('4')"
                            >
                                Ученик
                            </button>
                        </div>

                        <!-- Подсказка до выбора -->
                        <p v-if="!currentRole" class="role-hint">
                            Выберите тип регистрации, чтобы продолжить.
                        </p>

                        <!-- Подпись после выбора -->
                        <p v-else class="role-hint">
                            Тип регистрации: <strong>{{ currentRole === '1' ? 'Родитель' : 'Ученик' }}</strong>
                        </p>

                        <!-- Форма появляется только после выбора роли -->
                        <form v-if="currentRole" @submit.prevent="register">
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

                            <!-- Поле ИНН -->
                            <div class="b-popup__block">
                                <p :class="{ 'input-error--p': errors.inn }">
                                    ИНН
                                </p>
                                <div class="b-popup__block-right">
                                    <input
                                        id="inn"
                                        type="text"
                                        name="inn"
                                        v-model="user.inn"
                                        autocomplete="off"
                                        placeholder="ИНН"
                                        @input="validateInn"
                                        :class="{ 'input-error': errors.inn }"
                                    />
                                    <span v-if="errors.inn" class="error-text">
                                        {{ errors.inn }}
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
                            <div style="margin-top: 20px; font-size: 14px">
                                <input type="checkbox" checked id="checkbox_rules" />
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
                            <p v-if="message">{{ message }}</p>
                        </form>

                        <div class="b-popup__link-social">
                            <a href="/login" title="Вход">Вход</a>
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
        const user = ref({
            name: "",
            inn: "",
            email: "",
            password: "",
        });

        // роли: null — ничего не выбрано, 'parent' или 'student'
        const currentRole = ref(null);

        const message = ref("");
        const errors = ref({
            name: null,
            inn: null,
            email: null,
            password: null,
        });

        // CSRF, если нужно (Laravel и т.п.)
        const csrfToken =
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content") || "";

        // ====== Методы валидации полей ======
        const validateName = () => {
            if (!user.value.name) {
                errors.value.name = "Поле ФИО обязательно.";
            } else {
                errors.value.name = null;
            }
        };

        const validateInn = () => {
            const inn = user.value.inn.trim();
            if (!inn) {
                errors.value.inn = "Поле ИНН обязательно.";
            } else if (!/^\d+$/.test(inn)) {
                errors.value.inn = "ИНН должен содержать только цифры.";
            } else if (inn.length !== 10 && inn.length !== 12) {
                errors.value.inn = "ИНН должен содержать 10 или 12 цифр.";
            } else {
                errors.value.inn = null;
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
                errors.value.password =
                    "Пароль должен содержать не менее 5 символов.";
            } else {
                errors.value.password = null;
            }
        };

        const validateForm = () => {
            validateName();
            validateInn();
            validateEmail();
            validatePassword();

            return (
                !errors.value.name &&
                !errors.value.inn &&
                !errors.value.email &&
                !errors.value.password
            );
        };

        // Выбор роли
        const setRole = (role) => {
            currentRole.value = role; // будет '1' или '4'
        };

        const register = async () => {
            if (!currentRole.value) {
                message.value = "Сначала выберите тип регистрации.";
                return;
            }

            if (!validateForm()) {
                console.log("Форма содержит ошибки:", errors.value);
                return;
            }

            const payload = {
                name: user.value.name,
                inn: user.value.inn,
                email: user.value.email,
                password: user.value.password,
                role: currentRole.value,
            };

            console.log("Сохраняем данные в localStorage:", payload);
            localStorage.setItem("pendingUser", JSON.stringify(payload));

            try {
                console.log("📤 Отправляем данные:", payload);
                const response = await axios.post("/api/register", payload);

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
                    errors.value.email =
                        "Пользователь с таким email уже существует.";
                } else {
                    message.value = "Ошибка регистрации. Попробуйте позже.";
                }
            }
        };

        const logInput = () => {
            console.log("Текущее состояние формы:", user.value);
        };

        return {
            user,
            message,
            errors,
            csrfToken,
            currentRole,
            setRole,
            register,
            logInput,
            validateName,
            validateInn,
            validateEmail,
            validatePassword,
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

.input-error--p {
    color: red !important;
}

.error-text {
    color: red;
    font-size: 12px;
}

/* Стили переключателя ролей */
.role-switch {
    display: flex;
    gap: 10px;
    margin: 10px 0 10px;
}

.role-btn {
    width: 200px;
    padding: 12px 30px;
    border-radius: 20px;
    border: 1px solid #ccc;
    background: #f5f5f5;
    cursor: pointer;
    font-size: 14px;
}

.role-btn--active {
    border-color: #2b6cb0;
    background: #2b6cb0;
    color: #fff;
}

.role-hint {
    font-size: 13px;
    margin-bottom: 10px;
    color: #666;
    text-align: center;
}
</style>
