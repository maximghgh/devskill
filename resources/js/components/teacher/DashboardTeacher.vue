<template>
    <div class="center">
        <h2>Панель преподавателя</h2>
        <div class="info__block">
            <div class="info__cards">
                <div class="info__card">
                    <p class="info__text">Курсов</p>
                    <span class="info__desc">{{ courses.length }}</span>
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
                    <div v-if="courses.length" class="course__block">
                        <div
                            v-for="course in coursesPage"
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
                                        >{{ course.students_count ?? 0 }} студентов</span
                                    >
                                </div>
                                <span class="course__desc">{{ courseDateLabel(course) }}</span>
                            </div>
                            <a
                                :href="`/teacher/course/${course.id}`"
                                class="course__link"
                                >Открыть курс</a
                            >
                        </div>
                    </div>
                    <div v-if="totalPages > 1" class="pagination">
                        <button
                            v-for="p in totalPages"
                            :key="`course-${p}`"
                            :class="page === p ? 'pag--active' : 'pag'"
                            @click="page = p"
                        ></button>
                    </div>
                    <div v-if="!courses.length" class="course__empty">
                        Курсов нет.
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
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import "./style.css";

const courses = ref([]);
const pageSize = 2;
const page = ref(1);

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

const totalStudentsCount = computed(() =>
    courses.value.reduce(
        (total, course) => total + Number(course.students_count ?? 0),
        0
    )
);

const totalPages = computed(() =>
    Math.max(1, Math.ceil(courses.value.length / pageSize))
);
const coursesPage = computed(() => {
    const start = (page.value - 1) * pageSize;
    return courses.value.slice(start, start + pageSize);
});

function formatDate(value) {
    if (!value) return "";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);
    return date.toLocaleDateString("ru-RU");
}

function courseDateLabel(course) {
    if (course?.end_date) {
        return `Дата завершения: ${formatDate(course.end_date)}`;
    }

    if (course?.start_date) {
        return `Дата старта: ${formatDate(course.start_date)}`;
    }

    return "Дата не указана";
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

watch(courses, () => {
    if (page.value > totalPages.value) page.value = 1;
});

onMounted(async () => {
    await loadCourses();
});
</script>
