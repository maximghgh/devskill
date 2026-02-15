<template>
    <div class="center">
        <h2 class="courses_h2">
            Курс: {{ course?.course_name || "Без названия" }}
        </h2>
        <div class="info__course">
            <div class="info__card info__card--course">
                <p class="info__text">Всего студентов:</p>
                <span class="info__desc info__desc--s">{{ studentsCount }}</span>
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
                    :class="topicBlockClass(topic)"
                >
                    <div class="course-block__header">
                        <div class="course-block__info">
                            <h2 class="course-block__title">
                                {{ topic.title || "Без названия" }}
                            </h2>
                            <span
                                class="course-block__status-badge"
                                :class="topicBadgeClass(topic)"
                            >
                                {{ topicStatusLabel(topic) }}
                            </span>
                        </div>

                        <div class="course-block__controls">
                            <label class="switch">
                                <input
                                    class="switch__input"
                                    type="checkbox"
                                    :checked="isTopicActive(topic)"
                                    :disabled="statusSaving[topic.id]"
                                    @change="toggleTopicStatus(topic, $event)"
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
const statusSaving = ref({});

function normalizeTopicStatus(topic) {
    const status = topic?.status || "закрыт";
    return status === "активный" || status === "закрыт" ? status : "закрыт";
}

function isTopicActive(topic) {
    return normalizeTopicStatus(topic) === "активный";
}

function topicStatusLabel(topic) {
    return isTopicActive(topic) ? "Активный" : "Закрыт";
}

function topicBlockClass(topic) {
    return isTopicActive(topic) ? "course-block--active" : "course-block--closed";
}

function topicBadgeClass(topic) {
    return isTopicActive(topic)
        ? "course-block__status-badge--active"
        : "course-block__status-badge--closed";
}

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

function updateTopicInState(topicId, patch) {
    topics.value = topics.value.map((topic) => {
        if (topic.id !== topicId) return topic;
        return { ...topic, ...patch };
    });
}

async function toggleTopicStatus(topic, event) {
    const nextStatus = event.target.checked ? "активный" : "закрыт";
    const prevStatus = normalizeTopicStatus(topic);
    if (nextStatus === prevStatus) return;

    updateTopicInState(topic.id, { status: nextStatus });
    statusSaving.value = { ...statusSaving.value, [topic.id]: true };
    try {
        await axios.patch(`/api/topics/${topic.id}/status`, {
            status: nextStatus,
        });
    } catch (e) {
        console.error("Ошибка обновления статуса темы:", e);
        updateTopicInState(topic.id, { status: prevStatus });
    } finally {
        statusSaving.value = { ...statusSaving.value, [topic.id]: false };
    }
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
        topics.value = topicsWithChapters.map((topic) => ({
            ...topic,
            status: normalizeTopicStatus(topic),
        }));
    } catch (e) {
        console.error("Ошибка загрузки тем:", e);
        topics.value = [];
    }
}

onMounted(async () => {
    await Promise.all([loadCourse(), loadStudents(), loadTopics()]);
});
</script>
