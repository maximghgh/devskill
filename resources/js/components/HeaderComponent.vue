<template>
    <header class="header header_main" :class="{ active: menuOpen }">
        <div class="header__inner">
            <div class="header__logo">
                <a href="/">
                    <img
                        src="../../img/logo.svg"
                        class="header__logo"
                        alt="Логотип"
                    />
                </a>
            </div>
            <nav class="header__nav">
                <a href="/">Банк знаний</a>
                <a href="/catalog">Каталог</a>
                <a href="/training">Для учителей</a>
                <a href="/news">Новости</a>
                <a href="/contact">Контакты</a>
                <a href="/about">О нас</a>
            </nav>
            <div class="header__lk">
                <div class="personal-area personal-area_active">
                    <div class="personal-area__inner">
                        <div v-if="user" class="header__lk">
                            <a href="/cabinet" class="header__lk-img">
                                <!-- <img src="https://devskills.foncode.ru/img/nofotolk.png" width="40" height="40" alt="User avatar" /> -->
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
                            <a
                                href="#"
                                @click="logout"
                                class="personal-area__button personal-area__button--none"
                                >Выйти</a
                            >
                            <div class="header__lk-name">
                                <a
                                    href="/cabinet"
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
        <!-- Кнопка бургер-меню -->
        <div class="menu-btn">
            <div class="burger-menu" @click="toggleMenu">
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
    </header>
</template>

<script>
export default {
    name: "HeaderComponent",
    data() {
        return {
            menuOpen: false,
            user: null,
        };
    },
    methods: {
        toggleMenu() {
            this.menuOpen = !this.menuOpen;
        },
        logout() {
            localStorage.removeItem("user");
            this.user = null;
            window.location.href = "/";
        },
    },
    mounted() {
        const storedUser = localStorage.getItem("user");
        if (storedUser) {
            try {
                this.user = JSON.parse(storedUser);
            } catch (error) {
                console.error("Ошибка парсинга данных пользователя", error);
            }
        }
    },
};
</script>

<style scoped>
.header__logo {
    height: 90px;
}
.avatar__user {
    border-radius: 50%;
}
@media (max-width: 991px) {
    nav {
        position: absolute;
        top: 122px; /* например, на 60px ниже header */
        left: 0;
        width: 100%;
        height: auto; /* или нужная высота */
        /* Начальное состояние (меню закрыто) */
        margin-top: -200%;
        transform: scale(0);
        transform-origin: top center; /* точка, вокруг которой скейл */
        transition: transform 0.3s ease, margin-top 0.3s ease;
    }

    /* Когда меню активно (header получил класс active), 
     переводим в конечное состояние (меню открыто) */
    .header_main.active nav {
        margin-top: 0;
        transform: scale(1);
    }
    nav {
        z-index: 999;
    }
    header.header_main .header__nav {
        display: none;
        flex-direction: column;
        gap: 10px;
    }

    header.header_main.active .header__nav {
        display: flex;
        background-color: #ffffff;

        padding: 70px 20px;
        height: 100vh;
        justify-content: flex-start;
        align-items: flex-start;
    }

    .menu-btn {
        margin-top: 20px;
        margin-right: 15px;
        display: flex;
        gap: 4px;
        flex-direction: column;
        align-items: flex-end;
        z-index: 99999;
    }
    .burger-menu {
        cursor: pointer;
        width: 27px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .burger-menu div {
        width: 27px;
        height: 3px;
        border-radius: 100px;
        background: #b2b5d1;
        transition: 0.3s;
    }

    header.header_main.active .burger-menu div:nth-child(1) {
        transform: rotate(45deg) translate(5px, 5px);
    }

    header.header_main.active .burger-menu div:nth-child(2) {
        opacity: 0;
    }

    header.header_main.active .burger-menu div:nth-child(3) {
        transform: rotate(-45deg) translate(5px, -5px);
    }
}
@media (max-width: 450px) {
    .header__lk-name {
        display: none;
    }
    .personal-area__button--none{
        display: block;
    }
}
</style>
