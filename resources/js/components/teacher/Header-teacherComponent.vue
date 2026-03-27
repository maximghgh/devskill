<template>
    <header class="header header_main">
        <div class="header__inner">
            <div class="header__logos">
                <div class="logos">
                    <a href="/teacher">
                        <img
                            src="../../../img/logo.png"
                            class="header__logo"
                            alt="Логотип"
                            height="90"
                            width="90"
                        />
                    </a>
                    <a href="https://istu.ru" target="_blank" class="logos">
                        <img
                            src="../../../img/logo_istu.svg"
                            class="header__logo"
                            alt="Логотип ИжГТУ"
                            height="90"
                            width="90"
                        />
                    </a>
                </div>
            </div>
            <nav class="header__nav">
                <a href="/catalog">Каталог</a>
                <a href="/news">Новости</a>
                <a href="/contact">Контакты</a>
                <a href="/about">О нас</a>
            </nav>
            <div class="header__lk">
                <div class="personal-area personal-area_active">
                    <div class="personal-area__inner">
                        <!-- Если пользователь авторизован -->
                        <div v-if="user" class="header__lk">
                            <div class="header-user-menu" @click.stop="toggleUserMenu">
                                <button
                                    type="button"
                                    class="header-user-menu__btn"
                                    :aria-expanded="userMenuOpen ? 'true' : 'false'"
                                >
                                    <img
                                        :src="
                                            user.photo
                                                ? `/storage/${user.photo}`
                                                : '../../../img/nofotolk.png'
                                        "
                                        alt="Фото пользователя"
                                        width="60"
                                        height="60"
                                        class="avatar__user"
                                    />
                                    <span class="header-user-menu__name">{{ user.name }}</span>
                                    <span
                                        class="header-user-menu__caret"
                                        :class="{ 'is-open': userMenuOpen }"
                                    ></span>
                                </button>
                                <div
                                    v-show="userMenuOpen"
                                    class="header-user-menu__dropdown"
                                    @click.stop
                                >
                                    <a href="/teacher" class="header-user-menu__item">Панель преподавателя</a>
                                    <a href="/teacher/journal" class="header-user-menu__item">Журнал</a>
                                    <a href="/teacher/course" class="header-user-menu__item">Курсы</a>
                                    <a href="/teacher/mycourses" class="header-user-menu__item">Мои курсы</a>
                                    <a href="/teacher/profile" class="header-user-menu__item">Профиль</a>
                                    <a href="#" class="header-user-menu__item" @click.prevent="logout">Выйти</a>
                                </div>
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
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import { globalNotification } from "../../globalNotification";

const user = ref(null);
const userMenuOpen = ref(false);

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

function toggleUserMenu() {
    userMenuOpen.value = !userMenuOpen.value;
}

function closeUserMenu() {
    userMenuOpen.value = false;
}

async function logout() {
    try {
        await window.axios.post("/logout");
    } catch (error) {
        console.error("Ошибка выхода:", error);
    } finally {
        localStorage.removeItem("user");
        user.value = null;
        window.location.href = "/";
    }
}

watch(user, (newValue) => {
    if (newValue) {
        localStorage.setItem("user", JSON.stringify(newValue));
    }
});

onMounted(() => {
    loadUser();
    document.addEventListener("click", closeUserMenu);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", closeUserMenu);
});

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
