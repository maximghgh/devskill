<template>
    <header class="header header_main" :class="{ active: menuOpen }">
        <div class="header__inner">
            <div class="header__logo">
                <div class="logos">
                    <a href="/">
                        <img
                            src="../../img/logo.png"
                            class="header__logo"
                            alt="Логотип"
                            height="90"
                            width="90"
                        />
                    </a>
                    <a href="https://istu.ru" target="_blank" class="logos">
                        <img
                            src="../../img/logo_istu.svg"
                            class="header__logo"
                            alt="Логотип ИжГТУ"
                            height="90"
                            width="90"
                        />
                    </a>
                </div>
            </div>
            <nav class="header__nav">
                <a href="/catalog" :class="{ 'nav__link--active': isActive('/catalog') }">Каталог</a>
                <a href="/news"    :class="{ 'nav__link--active': isActive('/news') }">Новости</a>
                <a href="/contact" :class="{ 'nav__link--active': isActive('/contact') }">Контакты</a>
                <a href="/about"   :class="{ 'nav__link--active': isActive('/about') }">О нас</a>
            </nav>
            <div class="header__lk">
                <div class="personal-area personal-area_active">
                    <div class="personal-area__inner">
                        <div v-if="user" class="header__lk">
                            <div v-if="isPrivileged" class="header-user-menu" @click.stop="toggleUserMenu">
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
                                        width="40"
                                        height="40"
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
                                    <a
                                        v-for="(item, idx) in userMenuItems"
                                        :key="idx"
                                        :href="item.href || '#'"
                                        class="header-user-menu__item"
                                        @click.prevent="handleMenuItem(item)"
                                    >
                                        {{ item.label }}
                                    </a>
                                </div>
                            </div>
                            <template v-else>
                                <a href="/cabinet" class="header__lk-img">
                                    <img
                                        :src="
                                            user.photo
                                                ? `/storage/${user.photo}`
                                                : '../../../img/nofotolk.png'
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
                            </template>
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
      userMenuOpen: false,
      user: null,
      currentPath: window.location.pathname,
    };
  },
  methods: {
    toggleMenu() {
      this.menuOpen = !this.menuOpen;
    },
    async logout() {
      try {
        await window.axios.post("/logout");
      } catch (error) {
        console.error("Ошибка выхода:", error);
      } finally {
        localStorage.removeItem("user");
        this.user = null;
        window.location.href = "/";
      }
    },
    toggleUserMenu() {
      this.userMenuOpen = !this.userMenuOpen;
    },
    closeUserMenu() {
      this.userMenuOpen = false;
    },
    handleMenuItem(item) {
      if (item.action === "logout") {
        this.logout();
        return;
      }
      if (item.href) {
        window.location.href = item.href;
      }
    },

    // точное совпадение
    isActive(path) {
      return this.currentPath === path;
    },

    // если нужно подсвечивать и вложенные страницы (например /news/123):
    // isActive(path) {
    //   return this.currentPath === path || this.currentPath.startsWith(path + "/");
    // },
  },
  computed: {
    isPrivileged() {
      const role = Number(this.user?.role);
      return role === 2 || role === 3;
    },
    userMenuItems() {
      if (!this.user) return [];
      const role = Number(this.user.role);
      if (role === 3) {
        return [
          { label: "Профиль", href: "/admin/profile" },
          { label: "Панель администратора", href: "/admin" },
          { label: "Выйти", action: "logout" },
        ];
      }
      if (role === 2) {
        return [
          { label: "Панель преподавателя", href: "/teacher" },
          { label: "Журнал", href: "/teacher/journal" },
          { label: "Курс", href: "/teacher/course" },
          { label: "Мои курсы", href: "/teacher/mycourses" },
          { label: "Профиль", href: "/teacher/profile" },
          { label: "Выйти", action: "logout" },
        ];
      }
      return [];
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

    // если где-то используешь history.pushState/назад-вперёд
    window.addEventListener("popstate", () => {
      this.currentPath = window.location.pathname;
    });
    document.addEventListener("click", this.closeUserMenu);
  },
  beforeUnmount() {
    document.removeEventListener("click", this.closeUserMenu);
  },
};

</script>

<style scoped>
.nav__link--active {
  color: rgba(0, 92, 148, 1);
}

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
