<template>
    <div class="center">
        <h2 class="courses_h2">
            Курс: {{ course?.course_name || "Без названия" }}
        </h2>
        <div class="info__course">
            <div class="info__card info__card--center">
                <p class="info__text">Всего студентов:</p>
                <span class="info__desc">{{ studentsCount }}</span>
            </div>
            <div class="info__card info__card--course">
                <p class="info__text">Текущий блок:</p>
                <span class="info__desc info__desc--s">
                    {{ currentTopicTitle }}
                </span>
            </div>
            <div class="info__card info__card--course">
                <p class="info__text">Ближайшие занятия:</p>
                <span class="info__desc info__desc--s">
                    {{ courseDatesText }}
                </span>
            </div>
        </div>
        <section class="blocks">
            <h1 class="blocks__title">Блоки</h1>

            <div class="blocks__list">
                <article
                    v-for="topic in topics"
                    :key="topic.id"
                    class="course-block"
                    :class="topicBlockClass"
                >
                    <div class="course-block__header">
                        <div class="course-block__info">
                            <h2 class="course-block__title">
                                {{ topic.title || "Без названия" }}
                            </h2>
                            <span
                                class="course-block__status-badge"
                                :class="topicBadgeClass"
                            >
                                {{ courseStatusLabel }}
                            </span>
                        </div>

                        <div class="course-block__controls">
                            <label class="switch">
                                <input
                                    class="switch__input"
                                    type="checkbox"
                                    checked
                                />
                                <span class="switch__slider"></span>
                            </label>

                            <button
                                class="course-block__collapse"
                                type="button"
                                aria-expanded="true"
                            >
                                <span
                                    class="course-block__collapse-icon"
                                ></span>
                            </button>
                        </div>
                    </div>

                    <ul class="course-block__lessons">
                        <li
                            v-for="(chapter, index) in topic.chapters"
                            :key="chapter.id"
                            class="course-block__lesson"
                        >
                            <span class="course-block__lesson-title">
                                {{ index + 1 }} урок. {{ chapter.title }}
                            </span>
                            <button
                                class="course-block__lesson-edit"
                                type="button"
                                @click="openChapterEdit(chapter)"
                            >
                                Редактировать
                            </button>
                        </li>
                        <li
                            v-if="!topic.chapters?.length"
                            class="course-block__lesson"
                        >
                            <span class="course-block__lesson-title">
                                Уроков нет
                            </span>
                        </li>
                    </ul>
                </article>
                <div v-if="!topics.length" class="course__empty">
                    Темы отсутствуют.
                </div>
            </div>
        </section>
    </div>
    <EditChapterDialog
        v-model="showChapterModal"
        :chapter="selectedChapter"
        :topic-id="selectedChapter?.topic_id"
        @saved="onChapterUpdated"
    />
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import EditChapterDialog from "../admin/modal_admin/EditChapter.vue";

const courseId = (() => {
    const parts = window.location.pathname.split("/").filter(Boolean);
    const idx = parts.indexOf("course");
    if (idx === -1) return null;
    return parts[idx + 1] ?? null;
})();

const course = ref(null);
const topics = ref([]);
const studentsCount = ref(0);
const showChapterModal = ref(false);
const selectedChapter = ref(null);

const courseStatus = computed(() => {
    if (!course.value) return "current";
    const now = new Date();
    const start = course.value.start_date
        ? new Date(course.value.start_date)
        : null;
    const end = course.value.end_date ? new Date(course.value.end_date) : null;

    if (end && now > end) return "past";
    if (start && now < start) return "future";
    return "current";
});

const courseStatusLabel = computed(() => {
    if (courseStatus.value === "past") return "Завершен";
    if (courseStatus.value === "future") return "Закрыт";
    return "Активен";
});

const topicBlockClass = computed(() => {
    if (courseStatus.value === "past") return "course-block--completed";
    if (courseStatus.value === "future") return "course-block--closed";
    return "course-block--active";
});

const topicBadgeClass = computed(() => {
    if (courseStatus.value === "past")
        return "course-block__status-badge--completed";
    if (courseStatus.value === "future")
        return "course-block__status-badge--closed";
    return "course-block__status-badge--active";
});

const currentTopicTitle = computed(() => {
    if (!topics.value.length) return "—";
    return topics.value[0]?.title || "—";
});

const courseDatesText = computed(() => {
    if (!course.value) return "—";
    if (course.value.start_date && course.value.end_date) {
        return `С ${formatDate(course.value.start_date)} до ${formatDate(
            course.value.end_date
        )}`;
    }
    if (course.value.start_date) {
        return `Старт: ${formatDate(course.value.start_date)}`;
    }
    if (course.value.end_date) {
        return `Окончание: ${formatDate(course.value.end_date)}`;
    }
    return "—";
});

function formatDate(value) {
    if (!value) return "";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);
    return date.toLocaleDateString("ru-RU");
}

function openChapterEdit(chapter) {
    selectedChapter.value = chapter;
    showChapterModal.value = true;
}

function onChapterUpdated(payload) {
    const updated = payload?.chapter ?? payload;
    if (!updated?.id) {
        showChapterModal.value = false;
        selectedChapter.value = null;
        return;
    }
    topics.value = topics.value.map((topic) => {
        const nextChapters = Array.isArray(topic.chapters)
            ? topic.chapters.map((ch) =>
                  ch.id === updated.id ? updated : ch
              )
            : [];
        return { ...topic, chapters: nextChapters };
    });
    showChapterModal.value = false;
    selectedChapter.value = null;
}

async function loadCourse() {
    if (!courseId) return;
    try {
        const { data } = await axios.get(`/api/courses/${courseId}`);
        course.value = data?.course ?? data;
    } catch (e) {
        console.error("Ошибка загрузки курса:", e);
        course.value = null;
    }
}

async function loadStudents() {
    if (!courseId) return;
    try {
        const { data } = await axios.get(`/api/students/${courseId}`);
        const list = Array.isArray(data) ? data : [];
        studentsCount.value = list.length;
    } catch (e) {
        console.error("Ошибка загрузки студентов:", e);
        studentsCount.value = 0;
    }
}

async function loadTopics() {
    if (!courseId) return;
    try {
        const { data } = await axios.get(`/admin/course/${courseId}/topics`);
        const list = Array.isArray(data?.topics) ? data.topics : [];
        const topicsWithChapters = await Promise.all(
            list.map(async (topic) => {
                try {
                    const resp = await axios.get(
                        `/admin/topic/${topic.id}/chapters`
                    );
                    const chapters = Array.isArray(resp?.data?.chapters)
                        ? resp.data.chapters
                        : [];
                    return { ...topic, chapters };
                } catch (e) {
                    console.error(
                        "Ошибка загрузки глав для темы:",
                        topic.id,
                        e
                    );
                    return { ...topic, chapters: [] };
                }
            })
        );
        topics.value = topicsWithChapters;
    } catch (e) {
        console.error("Ошибка загрузки тем:", e);
        topics.value = [];
    }
}

onMounted(async () => {
    await Promise.all([loadCourse(), loadStudents(), loadTopics()]);
});
</script>
