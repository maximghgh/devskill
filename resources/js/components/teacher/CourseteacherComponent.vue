<template>
    <div class="center">
        <h2>Курсы</h2>
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
                                    >{{ course.students_count ?? 0 }} студентов</span
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
            <div class="main">
                <div class="div">
                    <h3>Будущие курсы</h3>
                    <div class="course__block course__block--s">
                        <div v-if="futureCourses.length">
                            <div
                                v-for="course in futureCoursesPage"
                                :key="course.id"
                                class="course_card"
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

                <div class="div">
                    <h3>Завершенные курсы</h3>
                    <div class="events__block course__block--s">
                        <div v-if="pastCourses.length" class="events__block course__block--s">
                            <div
                                v-for="course in pastCoursesPage"
                                :key="course.id"
                                class="course_card"
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

                                <span class="course__status course__status--yellow"
                                    >Завершен</span
                                >
                            </div>
                            <div v-if="pastTotalPages > 1" class="pagination">
                                <button
                                    v-for="p in pastTotalPages"
                                    :key="`past-${p}`"
                                    :class="pastPage === p ? 'pag--active' : 'pag'"
                                    @click="pastPage = p"
                                ></button>
                            </div>
                        </div>
                        <div v-else class="course__empty">Завершенных курсов нет.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import { watch } from "vue";

const courses = ref([]);
const pageSize = 4;
const currentPage = ref(1);
const futurePage = ref(1);
const pastPage = ref(1);

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
const pastCourses = computed(() =>
    courses.value.filter((c) => getCourseStatus(c) === "past")
);

const currentTotalPages = computed(() =>
    Math.max(1, Math.ceil(currentCourses.value.length / pageSize))
);
const futureTotalPages = computed(() =>
    Math.max(1, Math.ceil(futureCourses.value.length / pageSize))
);
const pastTotalPages = computed(() =>
    Math.max(1, Math.ceil(pastCourses.value.length / pageSize))
);

const currentCoursesPage = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return currentCourses.value.slice(start, start + pageSize);
});
const futureCoursesPage = computed(() => {
    const start = (futurePage.value - 1) * pageSize;
    return futureCourses.value.slice(start, start + pageSize);
});
const pastCoursesPage = computed(() => {
    const start = (pastPage.value - 1) * pageSize;
    return pastCourses.value.slice(start, start + pageSize);
});

watch([currentCourses, futureCourses, pastCourses], () => {
    if (currentPage.value > currentTotalPages.value) currentPage.value = 1;
    if (futurePage.value > futureTotalPages.value) futurePage.value = 1;
    if (pastPage.value > pastTotalPages.value) pastPage.value = 1;
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

onMounted(loadCourses);
</script>
