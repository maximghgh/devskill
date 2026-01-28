<template>
    <div class="center">
        <h2>Панель преподавателя</h2>
        <div class="info__block">
            <div class="info__cards">
                <div class="info__card">
                    <p class="info__text">Активных курсов</p>
                    <span class="info__desc">{{ activeCoursesCount }}</span>
                </div>
                <div class="info__card">
                    <p class="info__text">Всего студентов</p>
                    <span class="info__desc">{{ totalStudentsCount }}</span>
                </div>
            </div>
            <div class="info__events">
                <p class="info__text">Ближайшие занятия</p>
                <span class="info__events-desc"
                    >Веб- разработка: 12 ноября 18.00, 3 корпус Ижгту, 3-1</span
                >
                <span class="info__events-desc"
                    >Дизайн: 15 ноября 18.00, 3 корпус Ижгту, 3-1</span
                >
                <span class="info__events-desc"
                    >Дизайн: 15 ноября 18.00, 3 корпус Ижгту, 3-1</span
                >
            </div>
        </div>
        <div class="main">
            <div class="div">
                <h3>Текущие курсы</h3>
                <div class="course__block course__block--current">
                    <div v-if="currentCourses.length" class="course__block">
                        <div
                            v-for="course in currentCoursesPage"
                            :key="course.id"
                            class="course_card course_card--s"
                        >
                            <div class="course__text">
                                <h4 class="course__title">
                                    {{ course.course_name || "Без названия" }}
                                </h4>
                                <div class="course__info">
                                    <span class="course__desc"
                                        >{{ course.topics_count ?? 0 }} модулей</span
                                    >
                                    <span class="course__desc"
                                        >{{ studentsByCourse[course.id] ?? 0 }} студентов</span
                                    >
                                </div>
                                <span class="course__desc">
                                    {{
                                        course.end_date
                                            ? `Дата завершения: ${formatDate(course.end_date)}`
                                            : "Дата завершения не указана"
                                    }}
                                </span>
                            </div>
                            <a
                                :href="`/teacher/course/${course.id}`"
                                class="course__link"
                                >Открыть курс</a
                            >

                            <span class="course__status">Активен</span>
                        </div>
                    </div>
                    <div v-if="currentTotalPages > 1" class="pagination">
                        <button
                            v-for="p in currentTotalPages"
                            :key="`current-${p}`"
                            :class="currentPage === p ? 'pag--active' : 'pag'"
                            @click="currentPage = p"
                        ></button>
                    </div>
                    <div v-if="!currentCourses.length" class="course__empty">
                        Текущих курсов нет.
                    </div>
                </div>
            </div>
            
            <div class="div">
                <h3>Ближайшие мероприятия</h3>
                <div class="events__block">
                    <div class="course_card">
                        <div class="course__text">
                            <h4 class="course__title">
                                Выступление по разработке сайтов
                            </h4>
                            <span class="course__desc">Дата проведения: 22 ноября в 18.00</span>
                            <span class="course__desc">Адрес: Ижгту, Интеграл</span>
                        </div>
                    </div>
                    <div class="course_card">
                        <div class="course__text">
                            <h4 class="course__title">
                                Выступление по разработке сайтов
                            </h4>
                            <span class="course__desc">Дата проведения: 22 ноября в 18.00</span>
                            <span class="course__desc">Адрес: Ижгту, Интеграл</span>
                        </div>
                    </div>
                    <div class="course_card">
                        <div class="course__text">
                            <h4 class="course__title">
                                Выступление по разработке сайтов
                            </h4>
                            <span class="course__desc">Дата проведения: 22 ноября в 18.00</span>
                            <span class="course__desc">Адрес: Ижгту, Интеграл</span>
                        </div>
                    </div>
                    <div class="pagination">
                        <button class="pag--active"></button>
                        <button class="pag"></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="course__later">
            <div class="div">
                <h3>Будущие курсы</h3>
                <div class="course__block course__block--s">
                    <div v-if="futureCourses.length">
                        <div
                            v-for="course in futureCoursesPage"
                            :key="course.id"
                            class="course_card course_card--s"
                        >
                            <div class="course__text">
                                <h4 class="course__title">
                                    {{ course.course_name || "Без названия" }}
                                </h4>
                                <div class="course__info">
                                    <span class="course__desc"
                                        >{{ course.topics_count ?? 0 }} модулей</span
                                    >
                                    <span class="course__desc"
                                        >{{ studentsByCourse[course.id] ?? 0 }} студентов</span
                                    >
                                </div>
                                <span class="course__desc">
                                    {{
                                        course.start_date
                                            ? `Дата старта: ${formatDate(course.start_date)}`
                                            : "Дата старта не указана"
                                    }}
                                </span>
                            </div>
                            <a
                                :href="`/teacher/course/${course.id}`"
                                class="course__link"
                                >Открыть курс</a
                            >

                            <span class="course__status course__status--red"
                                >Закрыт</span
                            >
                        </div>
                        <div v-if="futureTotalPages > 1" class="pagination">
                            <button
                                v-for="p in futureTotalPages"
                                :key="`future-${p}`"
                                :class="futurePage === p ? 'pag--active' : 'pag'"
                                @click="futurePage = p"
                            ></button>
                        </div>
                    </div>
                    <div v-else class="course__empty">Будущих курсов нет.</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import "./style.css";

const courses = ref([]);
const studentsByCourse = ref({});
const totalStudentsCount = ref(0);
const pageSize = 2;
const currentPage = ref(1);
const futurePage = ref(1);

function getTeacherId() {
    const stored = localStorage.getItem("user");
    if (!stored) return null;
    try {
        const parsed = JSON.parse(stored);
        return parsed?.id ?? null;
    } catch (e) {
        return null;
    }
}

function parseTeacherIds(teachers) {
    if (Array.isArray(teachers)) return teachers;
    if (teachers == null) return [];
    if (typeof teachers === "number") return [teachers];
    if (typeof teachers === "string") {
        try {
            const parsed = JSON.parse(teachers);
            if (Array.isArray(parsed)) return parsed;
            if (parsed == null) return [];
            return [parsed];
        } catch {
            return [];
        }
    }
    return [];
}

function getCourseStatus(course) {
    const now = new Date();
    const start = course.start_date ? new Date(course.start_date) : null;
    const end = course.end_date ? new Date(course.end_date) : null;

    if (end && now > end) return "past";
    if (start && now < start) return "future";
    return "current";
}

const currentCourses = computed(() =>
    courses.value.filter((c) => getCourseStatus(c) === "current")
);
const futureCourses = computed(() =>
    courses.value.filter((c) => getCourseStatus(c) === "future")
);

const activeCoursesCount = computed(() => currentCourses.value.length);

const currentTotalPages = computed(() =>
    Math.max(1, Math.ceil(currentCourses.value.length / pageSize))
);
const futureTotalPages = computed(() =>
    Math.max(1, Math.ceil(futureCourses.value.length / pageSize))
);

const currentCoursesPage = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return currentCourses.value.slice(start, start + pageSize);
});
const futureCoursesPage = computed(() => {
    const start = (futurePage.value - 1) * pageSize;
    return futureCourses.value.slice(start, start + pageSize);
});

function formatDate(value) {
    if (!value) return "";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);
    return date.toLocaleDateString("ru-RU");
}

async function loadCourses() {
    try {
        const teacherId = getTeacherId();
        const { data } = await axios.get("/api/courses");
        const list = Array.isArray(data) ? data : data?.data || [];

        if (!teacherId) {
            courses.value = [];
            return;
        }

        const teacherIdStr = String(teacherId);
        courses.value = list.filter((course) => {
            const ids = parseTeacherIds(course.teachers).map((id) =>
                String(id)
            );
            return ids.includes(teacherIdStr);
        });
    } catch (e) {
        console.error("Ошибка при загрузке курсов:", e);
        courses.value = [];
    }
}

async function loadStudentsCounts() {
    const next = {};
    let total = 0;
    await Promise.all(
        courses.value.map(async (course) => {
            try {
                const { data } = await axios.get(`/api/students/${course.id}`);
                const list = Array.isArray(data) ? data : [];
                next[course.id] = list.length;
                total += list.length;
            } catch (e) {
                next[course.id] = 0;
            }
        })
    );
    studentsByCourse.value = next;
    totalStudentsCount.value = total;
}

watch(courses, () => {
    if (currentPage.value > currentTotalPages.value) currentPage.value = 1;
    if (futurePage.value > futureTotalPages.value) futurePage.value = 1;
});

onMounted(async () => {
    await loadCourses();
    await loadStudentsCounts();
});
</script>
