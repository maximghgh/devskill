<template>
    <div class="center">
        <h2 class="courses_h2">
            Курс: {{ course?.course_name || "Без названия" }}
        </h2>

        <!-- Выбор группы: набор открытых тем у каждой группы свой -->
        <section class="group-scope">
            <div class="group-scope__row">
                <label class="group-scope__label" for="course-scope-group">
                    Группа
                </label>
                <select
                    id="course-scope-group"
                    class="group-scope__select"
                    v-model="selectedGroupId"
                    :disabled="groupsLoading"
                >
                    <option :value="null">Все ученики</option>
                    <option
                        v-for="group in groups"
                        :key="group.id"
                        :value="group.id"
                    >
                        {{ group.name_group }}
                    </option>
                </select>
                <span
                    class="group-scope__badge"
                    :class="
                        openTopicsCount
                            ? 'group-scope__badge--active'
                            : 'group-scope__badge--empty'
                    "
                >
                    Открыто {{ openTopicsCount }} из {{ topics.length }}
                </span>
            </div>

            <p class="group-scope__hint">{{ scopeHint }}</p>
        </section>

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

// Группы курса и выбранная область настройки.
// null — базовый статус темы (для тех, кто не состоит ни в одной группе).
const groups = ref([]);
const groupsLoading = ref(false);
const selectedGroupId = ref(null);

const selectedGroup = computed(
    () => groups.value.find((g) => g.id === selectedGroupId.value) || null
);

/** id тем, открытых выбранной группе. */
const openTopicIds = computed(
    () => new Set(selectedGroup.value?.topic_ids ?? [])
);

const scopeHint = computed(() => {
    if (groupsLoading.value) {
        return "Загружаем группы курса...";
    }
    if (!groups.value.length) {
        return "На курсе пока нет групп — переключатели задают статус темы для всех учеников.";
    }
    if (!selectedGroup.value) {
        return "Переключатели задают статус темы для учеников вне групп.";
    }
    return `Отмеченные темы открыты только ученикам группы «${selectedGroup.value.name_group}».`;
});

function normalizeTopicStatus(topic) {
    const status = topic?.status || "закрыт";
    return status === "активный" || status === "закрыт" ? status : "закрыт";
}

function isTopicActive(topic) {
    if (selectedGroup.value) {
        return openTopicIds.value.has(topic.id);
    }
    return normalizeTopicStatus(topic) === "активный";
}

/** Сколько тем открыто в выбранной области — для счётчика в шапке. */
const openTopicsCount = computed(
    () => topics.value.filter((topic) => isTopicActive(topic)).length
);

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

function setGroupTopicIds(groupId, topicIds) {
    groups.value = groups.value.map((group) =>
        group.id === groupId ? { ...group, topic_ids: topicIds } : group
    );
}

/**
 * Переключатель темы. Если выбрана группа — меняем её набор открытых тем,
 * иначе правим базовый статус самой темы.
 */
async function toggleTopicStatus(topic, event) {
    const shouldOpen = event.target.checked;
    statusSaving.value = { ...statusSaving.value, [topic.id]: true };

    try {
        if (selectedGroup.value) {
            await toggleTopicForGroup(topic, shouldOpen);
        } else {
            await toggleTopicGlobally(topic, shouldOpen);
        }
    } finally {
        statusSaving.value = { ...statusSaving.value, [topic.id]: false };
    }
}

async function toggleTopicForGroup(topic, shouldOpen) {
    const group = selectedGroup.value;
    const prevIds = group.topic_ids ?? [];
    const nextIds = shouldOpen
        ? [...new Set([...prevIds, topic.id])]
        : prevIds.filter((id) => id !== topic.id);

    setGroupTopicIds(group.id, nextIds);
    try {
        await axios.put(
            `/api/admin/course/${courseId}/groups/${group.id}/topics`,
            { topic_ids: nextIds }
        );
    } catch (e) {
        console.error("Ошибка обновления тем группы:", e);
        setGroupTopicIds(group.id, prevIds);
    }
}

async function toggleTopicGlobally(topic, shouldOpen) {
    const nextStatus = shouldOpen ? "активный" : "закрыт";
    const prevStatus = normalizeTopicStatus(topic);
    if (nextStatus === prevStatus) return;

    updateTopicInState(topic.id, { status: nextStatus });
    try {
        await axios.patch(`/api/topics/${topic.id}/status`, {
            status: nextStatus,
        });
    } catch (e) {
        console.error("Ошибка обновления статуса темы:", e);
        updateTopicInState(topic.id, { status: prevStatus });
    }
}

async function loadGroups() {
    if (!courseId) return;
    groupsLoading.value = true;
    try {
        const { data } = await axios.get(
            `/api/admin/course/${courseId}/groups`
        );
        const list = Array.isArray(data) ? data : [];
        groups.value = list.map((group) => ({
            ...group,
            topic_ids: Array.isArray(group.topic_ids) ? group.topic_ids : [],
        }));
    } catch (e) {
        console.error("Ошибка загрузки групп:", e);
        groups.value = [];
    } finally {
        groupsLoading.value = false;
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
    await Promise.all([
        loadCourse(),
        loadStudents(),
        loadTopics(),
        loadGroups(),
    ]);
});
</script>

<style scoped>
.group-scope {
    margin: 16px 0 32px;
}

.group-scope__row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.group-scope__label {
    font-family: JanoSansProRegular;
    font-size: 16px;
    color: #858585;
}

/* Селект и бейдж — одна высота, чтобы строка читалась ровной */
.group-scope__select,
.group-scope__badge {
    box-sizing: border-box;
    height: 42px;
    border-radius: 21px;
    font-family: JanoSansProRegular;
    font-size: 16px;
}

.group-scope__select {
    flex: 0 1 300px;
    min-width: 220px;
    color: #121212;
    padding: 0 42px 0 18px;
    border: 1px solid #d9c7ec;
    background-color: #ffffff;
    background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L6 6L11 1' stroke='%237A2ABD' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 12px 8px;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
    transition: border-color var(--transition-3, 0.3s);
}

.group-scope__select option {
    font-family: JanoSansProRegular;
}

.group-scope__select:hover:not(:disabled) {
    border-color: #7a2abd;
}

/* Специфичность выше глобального `:focus { border: 0 }` в app.css */
.group-scope__select:focus {
    outline: none;
    border: 1px solid #7a2abd;
}

.group-scope__select:disabled {
    cursor: default;
    color: #858585;
    border-color: #e8e6f0;
    background-color: #f6f5fb;
}

.group-scope__badge {
    display: inline-flex;
    align-items: center;
    padding: 0 18px;
    white-space: nowrap;
    color: #121212;
}

.group-scope__badge--active {
    background-color: #bde5b0;
}

.group-scope__badge--empty {
    background-color: #e5b0b0;
}

.group-scope__hint {
    margin: 10px 0 0;
    font-family: JanoSansProLight;
    font-size: 14px;
    line-height: 146%;
    color: #858585;
}

@media screen and (max-width: 575.98px) {
    .group-scope__select {
        flex: 1 1 100%;
    }
}
</style>
