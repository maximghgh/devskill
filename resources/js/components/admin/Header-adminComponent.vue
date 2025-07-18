<template>
    <header class="header header_main">
        <div class="header__inner">
            <div class="header__logos">
                <a href="/admin">
                    <img
                        src="../../../img/logo.svg"
                        class="header__logo"
                        alt="Логотип"
                        height="90"
                        width="90"
                    />
                </a>
            </div>
            <h3>Панель администратора</h3>
            <div class="header__lk">
                <div class="personal-area personal-area_active">
                    <div class="personal-area__inner">
                        <!-- Если пользователь авторизован -->
                        <div v-if="user" class="header__lk">
                            <a href="/profile" class="header__lk-img">
                                <img
                                    :src="
                                        user.photo
                                            ? `/storage/${user.photo}`
                                            : 'https://devskills.foncode.ru/img/nofotolk.png'
                                    "
                                    alt="Фото пользователя"
                                    width="40"
                                    height="40"
                                    class="avatar__user"
                                />
                            </a>
                            <div class="header__lk-name">
                                <a
                                    href="/admin/profile"
                                    class="personal-area__username"
                                    >{{ user.name }}</a
                                >
                                <a
                                    href="#"
                                    @click.prevent="logout"
                                    class="personal-area__button"
                                    >Выйти</a
                                >
                            </div>
                        </div>
                        <!-- Если пользователь не авторизован -->
                        <div v-else>
                            <a href="/login" class="personal-area__username"
                                >Войти</a
                            >
                            <a href="/register" class="personal-area__username"
                                >Регистрация</a
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Блок уведомления с динамическим классом -->
        <transition name="fade">
            <div
                class="modal__message"
                :class="{
                    modal__message_error: globalNotification.type === 'error',
                    modal__message_success:
                        globalNotification.type === 'success',
                }"
                v-if="globalNotification.categoryMessage"
            >
                <button
                    :class="{
                        'close-button_error':
                            globalNotification.type === 'error',
                        'close-button_success':
                            globalNotification.type === 'success',
                    }"
                    @click="closeNotification"
                >
                    ×
                </button>
                <p class="message">{{ globalNotification.categoryMessage }}</p>
            </div>
        </transition>
    </header>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { globalNotification } from "../../globalNotification";

const user = ref(null);

function loadUser() {
    const storedUser = localStorage.getItem("user");
    if (storedUser) {
        try {
            user.value = JSON.parse(storedUser);
        } catch (error) {
            console.error("Ошибка при загрузке пользователя:", error);
        }
    }
}

function logout() {
    localStorage.removeItem("user");
    user.value = null;
    window.location.href = "/";
}

watch(user, (newValue) => {
    if (newValue) {
        localStorage.setItem("user", JSON.stringify(newValue));
    }
});

onMounted(loadUser);

function closeNotification() {
    globalNotification.categoryMessage = "";
}

watch(
    () => globalNotification.categoryMessage,
    (newMessage) => {
        if (newMessage) {
            setTimeout(() => {
                globalNotification.categoryMessage = "";
            }, 3000);
        }
    }
);
</script>

<style scoped>
.avatar__user{
    border-radius: 50%;
}
.header__logo {
    height: 90px;
}
/* Стили для успешного уведомления */
.modal__message_success {
    display: flex;
    align-items: center;
    padding: 20px;
    font-size: 15px;
    border-radius: 7px;
    background-color: rgba(1, 192, 1, 0.192);
    width: 300px;
    min-height: 50px;
    position: fixed;
    top: 20px;
    right: 20px;
}

/* Стили для уведомления об ошибке */
.modal__message_error {
    display: flex;
    align-items: center;
    padding: 20px;
    font-size: 15px;
    border-radius: 7px;
    background-color: rgba(255, 0, 0, 0.2); /* красный оттенок */
    width: 300px;
    min-height: 50px;
    position: fixed;
    top: 20px;
    right: 20px;
    color: rgb(97, 2, 2);
}

.close-button_success {
    position: absolute;
    top: 5px;
    right: 5px;
    background: transparent;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: green;
}
.close-button_error {
    position: absolute;
    top: 5px;
    right: 5px;
    background: transparent;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: red;
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
.fade-enter-to,
.fade-leave-from {
    opacity: 1;
}
</style>
