<template>
    <div>
        <div class="containers">
            <aside>
                <div class="menus">
                    <div class="aside__menu">
                        <ul class="aside__list">
                            <li
                                class="aside__item"
                                v-for="(item, index) in menuItems"
                                :key="index"
                            >
                                <a
                                    :href="item.href"
                                    :class="[
                                        'aside__link',
                                        {
                                            'aside__link--active':
                                                activeIndex === index,
                                        },
                                    ]"
                                    @click.prevent="setActive(index)"
                                >
                                    <span class="aside__icon" v-if="item.icon">
                                        <img
                                            :src="item.icon"
                                            width="24"
                                            height="24"
                                            alt=""
                                        />
                                    </span>
                                    <span class="aside__text">{{
                                        item.label
                                    }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="menu-block" style="margin-top: 20px">
                        <!-- ДАШБОРД -->
                        <AdminDashboardBlock
                            v-show="activeId === 'dashboard'"
                            id="dashboard"
                            :current-user="user"
                            :users-count="users.length"
                            :courses-count="courses.length"
                        />

                        <!-- ПОЛЬЗОВАТЕЛИ -->
                        <AdminUsersBlock
                            v-show="activeId === 'users'"
                            id="users"
                            v-model:users="users"
                        />

                        <!-- КУРСЫ -->
                        <AdminCoursesBlock
                            v-show="activeId === 'courses'"
                            id="courses"
                            v-model:courses="courses"
                            :users="users"
                            @requestCreateCourse="openCreateCourseDialog"
                            @requestEditCourse="openEditCourseDialog"
                        />

                        <!-- КАТЕГОРИИ -->
                        <AdminCategoriesBlock
                            v-show="activeId === 'other'"
                            id="other"
                            v-model:languages="languages"
                            v-model:directions="directions"
                        />

                        <!-- НОВОСТИ -->
                        <AdminNewsBlock
                            v-show="activeId === 'news'"
                            id="news"
                            v-model:newsItems="newsItems"
                        />

                        <!-- FAQ -->
                        <AdminFaqBlock
                            v-show="activeId === 'faq'"
                            id="faq"
                            v-model:faqs="faqs"
                        />
                    </div>
                </div>
            </aside>
        </div>

        <!-- Диалоги курсов (как у тебя было) -->
        <CreateCourseDialog v-model="showCreateCourse" @saved="loadCourses" />
        <EditCourseDialog
            v-model="showEditCourse"
            :course="selectedCourse"
            @saved="loadCourses"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import "./style.css";

import { globalNotification } from "../../globalNotification";

import AdminDashboardBlock from "./blocks/AdminDashboardBlock.vue";
import AdminUsersBlock from "./blocks/AdminUsersBlock.vue";
import AdminCoursesBlock from "./blocks/AdminCoursesBlock.vue";
import AdminCategoriesBlock from "./blocks/AdminCategoriesBlock.vue";
import AdminNewsBlock from "./blocks/AdminNewsBlock.vue";
import AdminFaqBlock from "./blocks/AdminFaqBlock.vue";

import CreateCourseDialog from "./CreateHackathon.vue";
import EditCourseDialog from "./EditCourseDialog.vue";

import iconDashboard from "./../../../img/admin/info.svg";
import iconUsers from "./../../../img/admin/users.svg";
import iconCourses from "./../../../img/admin/couse.svg";
import iconCategory from "./../../../img/admin/couse.svg";
import iconNews from "./../../../img/admin/newspaper.svg";
import iconFaq from "./../../../img/admin/newspaper.svg";

const showCreateCourse = ref(false);
const showEditCourse = ref(false);
const selectedCourse = ref(null);

function openCreateCourseDialog() {
    showCreateCourse.value = true;
}
function openEditCourseDialog(course) {
    selectedCourse.value = course;
    showEditCourse.value = true;
}

/* ====== меню ====== */
const activeIndex = ref(0);
const menuItems = [
    {
        id: "dashboard",
        label: "Дашборд",
        href: "#dashboard",
        icon: iconDashboard,
    },
    { id: "users", label: "Пользователи", href: "#users", icon: iconUsers },
    { id: "courses", label: "Курсы", href: "#courses", icon: iconCourses },
    { id: "other", label: "Категории", href: "#other", icon: iconCategory },
    { id: "news", label: "Новости", href: "#news", icon: iconNews },
    { id: "faq", label: "Частые вопросы", href: "#faq", icon: iconFaq },
];

const activeId = computed(
    () => menuItems[activeIndex.value]?.id || "dashboard"
);

function saveActiveIndex() {
    localStorage.setItem("activeIndex", String(activeIndex.value));
}
function setActive(index) {
    activeIndex.value = index;
    saveActiveIndex();
    const target = document.getElementById(menuItems[index].id);
    if (target) target.scrollIntoView({ behavior: "smooth" });
}

/* ====== данные ====== */
const user = ref(null);

const users = ref([]);
const courses = ref([]);
const languages = ref([]);
const directions = ref([]);
const newsItems = ref([]);
const faqs = ref([]);

function base64ToUtf8(str) {
    return decodeURIComponent(escape(atob(str)));
}

async function loadUsers() {
    try {
        const { data } = await axios.get("/api/users");
        users.value = data;
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage =
            "Ошибка при загрузке пользователей";
        globalNotification.type = "error";
    }
}
async function loadCourses() {
    try {
        const { data } = await axios.get("/api/courses");
        courses.value = data;
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка при загрузке курсов";
        globalNotification.type = "error";
    }
}
async function loadLanguages() {
    try {
        const { data } = await axios.get("/api/languages");
        languages.value = data;
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка при загрузке языков";
        globalNotification.type = "error";
    }
}
async function loadDirections() {
    try {
        const { data } = await axios.get("/api/directions");
        directions.value = data;
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка при загрузке направлений";
        globalNotification.type = "error";
    }
}
async function loadNews() {
    try {
        const { data } = await axios.get("/api/news");
        newsItems.value = data;
    } catch (e) {
        console.error(e);
        globalNotification.categoryMessage = "Ошибка при загрузке новостей";
        globalNotification.type = "error";
    }
}
async function loadFaqs() {
    try {
        const { data } = await axios.get("/api/faqs");
        faqs.value = data;
    } catch (e) {
        console.error(e);
    }
}

onMounted(async () => {
    const savedIndex = localStorage.getItem("activeIndex");
    if (savedIndex !== null) activeIndex.value = parseInt(savedIndex, 10);

    // verifiedUser в URL
    const urlParams = new URLSearchParams(window.location.search);
    const verifiedUserParam = urlParams.get("verifiedUser");
    if (verifiedUserParam) {
        try {
            const pureBase64 = decodeURIComponent(verifiedUserParam);
            const decoded = base64ToUtf8(pureBase64);
            const verifiedUserData = JSON.parse(decoded);
            localStorage.setItem("user", JSON.stringify(verifiedUserData));
            localStorage.removeItem("pendingUser");
        } catch (e) {
            console.error(e);
            globalNotification.categoryMessage =
                "Ошибка при подтверждении email";
            globalNotification.type = "error";
        }
    }

    const storedUser = localStorage.getItem("user");
    if (storedUser) {
        try {
            user.value = JSON.parse(storedUser);
        } catch {}
    }

    await Promise.all([
        loadUsers(),
        loadCourses(),
        loadLanguages(),
        loadDirections(),
        loadNews(),
        loadFaqs(),
    ]);
});
</script>
