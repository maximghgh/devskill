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
                <button
                    v-if="!scheduleEditing"
                    type="button"
                    class="schedule__edit"
                    title="Редактировать расписание"
                    aria-label="Редактировать расписание"
                    @click="startEditSchedule"
                >
                    <svg
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M12 20h9" />
                        <path
                            d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"
                        />
                    </svg>
                </button>

                <p class="info__text">Расписание</p>

                <textarea
                    v-if="scheduleEditing"
                    ref="scheduleInput"
                    v-model="scheduleDraft"
                    class="schedule__input"
                    rows="2"
                    :disabled="scheduleSaving"
                    placeholder="Например:&#10;Веб-разработка: 12 ноября 18.00, 3 корпус Ижгту, ауд. 3-1&#10;Дизайн: 15 ноября 18.00, 3 корпус Ижгту, ауд. 3-1"
                    @input="autoGrowSchedule"
                    @keydown.esc="cancelEditSchedule"
                    @keydown.enter.ctrl.prevent="saveSchedule"
                    @keydown.enter.meta.prevent="saveSchedule"
                ></textarea>

                <div v-if="scheduleEditing" class="schedule__actions">
                    <button
                        type="button"
                        class="schedule__btn schedule__btn--primary"
                        :disabled="scheduleSaving"
                        @click="saveSchedule"
                    >
                        {{ scheduleSaving ? "Сохранение..." : "Сохранить" }}
                    </button>
                    <button
                        type="button"
                        class="schedule__btn"
                        :disabled="scheduleSaving"
                        @click="cancelEditSchedule"
                    >
                        Отмена
                    </button>
                </div>

                <p
                    v-else-if="scheduleText"
                    class="info__events-desc schedule__text"
                >
                    {{ scheduleText }}
                </p>
                <span v-else class="info__events-desc">
                    Расписание не заполнено
                </span>
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
import { ref, computed, onMounted, watch, nextTick } from "vue";
import axios from "axios";
import "./style.css";

const courses = ref([]);
const pageSize = 2;
const page = ref(1);

/* --- Расписание преподавателя (редактируемый текст) --- */
const scheduleText = ref("");
const scheduleDraft = ref("");
const scheduleEditing = ref(false);
const scheduleSaving = ref(false);

const scheduleInput = ref(null);

async function startEditSchedule() {
    scheduleDraft.value = scheduleText.value;
    scheduleEditing.value = true;
    await nextTick();
    scheduleInput.value?.focus();
    autoGrowSchedule();
}

/** Высота поля подстраивается под количество строк расписания. */
function autoGrowSchedule() {
    const el = scheduleInput.value;
    if (!el) return;
    el.style.height = "auto";
    el.style.height = `${el.scrollHeight}px`;
}

function cancelEditSchedule() {
    scheduleEditing.value = false;
    scheduleDraft.value = scheduleText.value;
}

async function loadSchedule() {
    const teacherId = getTeacherId();
    if (!teacherId) return;
    try {
        const { data } = await axios.get(`/api/teacher/${teacherId}/schedule`);
        scheduleText.value = data?.schedule || "";
    } catch (e) {
        console.error("Ошибка при загрузке расписания:", e);
    }
}

async function saveSchedule() {
    // Сохранение вызывается кнопкой и по Ctrl+Enter — второй раз не шлём.
    if (!scheduleEditing.value || scheduleSaving.value) return;

    if (scheduleDraft.value === scheduleText.value) {
        scheduleEditing.value = false;
        return;
    }

    const teacherId = getTeacherId();
    if (!teacherId) return;
    scheduleSaving.value = true;
    try {
        const { data } = await axios.post(`/api/teacher/${teacherId}/schedule`, {
            schedule: scheduleDraft.value,
        });
        scheduleText.value = data?.schedule ?? scheduleDraft.value;
        scheduleEditing.value = false;
    } catch (e) {
        console.error("Ошибка при сохранении расписания:", e);
    } finally {
        scheduleSaving.value = false;
    }
}

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
    await loadSchedule();
});
</script>

<style scoped>
/* Содержимое прижато к верху: в глобальных стилях у блока
   justify-content: center, из-за чего расписание висело по центру. */
.info__events {
    position: relative;
    justify-content: flex-start;
}

/* Карандаш в правом верхнем углу блока.
   z-index обязателен: соседние flex-элементы рисуются как позиционированные
   и без него перекрывают кнопку — кликалась только незакрытая часть. */
.schedule__edit {
    position: absolute;
    z-index: 1;
    top: 12px;
    right: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    padding: 0;
    border: none;
    border-radius: 8px;
    background: transparent;
    color: #6c5ce7;
    cursor: pointer;
    opacity: 0.7;
    transition: background 0.15s, opacity 0.15s;
}
.schedule__edit:hover {
    background: rgba(108, 92, 231, 0.12);
    opacity: 1;
}

.schedule__text {
    white-space: pre-line;
}

/* поле редактирования на месте текста расписания */
.schedule__input {
    width: 100%;
    box-sizing: border-box;
    display: block;
    resize: vertical;
    overflow: hidden;
    min-height: 44px;
    padding: 6px 8px;
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    font: inherit;
    color: inherit;
    background: #fff;
    outline: none;
}
.schedule__input:focus {
    border-color: #6c5ce7;
}
.schedule__input:disabled {
    opacity: 0.6;
}

/* Кнопки редактирования: сохранение больше не происходит молча по потере фокуса */
.schedule__actions {
    display: flex;
    gap: 10px;
    margin-top: 2px;
}

.schedule__btn {
    box-sizing: border-box;
    height: 38px;
    padding: 0 20px;
    border: 1px solid #d9c7ec;
    border-radius: 19px;
    background: #ffffff;
    color: var(--primary-600, #4e187b);
    font-family: JanoSansProRegular;
    font-size: 16px;
    cursor: pointer;
    transition: background var(--transition-3, 0.3s),
        border-color var(--transition-3, 0.3s);
}

.schedule__btn:hover:not(:disabled) {
    border-color: #7a2abd;
}

.schedule__btn--primary {
    border-color: #7a2abd;
    background: #7a2abd;
    color: #ffffff;
}

.schedule__btn--primary:hover:not(:disabled) {
    background: #68219f;
    border-color: #68219f;
}

.schedule__btn:disabled {
    cursor: default;
    opacity: 0.6;
}
</style>
