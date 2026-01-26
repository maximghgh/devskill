<template>
    <div class="maincontainer">
        <div class="backs">
            <button @click="goBack" class="btn-back">
                Вернуться в личный кабинет
            </button>
        </div>
        <div class="container flex">
            <!-- Левый сайдбар -->
            <aside class="sidebar">
                <!-- Карточка курса -->
                <div class="card course-card">
                    <div class="course-card__header">
                        <div>
                            <div class="course-card__label">Курс</div>
                            <h2 class="course-card__title">
                                {{ course.card_title }}
                            </h2>
                            <div class="course-card__sub">
                                С финальной работой
                            </div>
                        </div>
                        <img
                            :src="
                                `../${course.card_image}` ||
                                '/img/logo_placeholder.png'
                            "
                            alt="Изображение курса"
                            width="93"
                            height="92"
                            class="logo_couerses"
                        />
                    </div>
                    <div class="progres">
                        <div class="course-card__sub">
                            Ваш прогресс: {{ progressPercentage }}%
                        </div>
                        <div class="progress-bar">
                            <span
                                :style="{ width: progressPercentage + '%' }"
                            ></span>
                        </div>
                    </div>
                </div>

                <!-- Telegram-чат -->
                <div class="card telegram-card">
                    <div class="telegram-card__header">
                        <h3>
                            Напишите на почту, если у вас появились проблемы
                        </h3>
                    </div>
                    <p>
                        Мы быстро с вами свяжемся и поможем
                        <a href="#">devskillreport@mail.ru</a>.
                    </p>
                </div>

                <div v-if="maxQr.image" class="card max-qr-card">
                    <p class="max-qr-card__title">
                        Напишите на почту, если у вас появились проблемы или
                        воспользуйтесь
                    </p>
                    <div class="max-qr-card__qr">
                        <img :src="maxQr.image" alt="QR код MAX" />
                    </div>
                    <p class="max-qr-card__note">
                        Мы быстро с вами свяжемся и поможем
                    </p>
                    <a
                        class="max-qr-card__link"
                        :href="maxQr.link"
                        target="_blank"
                        rel="noopener"
                    >
                        Ссылка на Max
                    </a>
                </div>
            </aside>

            <!-- Правый контент -->
            <div class="content">
                <ul class="topics-list">
                    <li
                        v-for="(topic, idx) in topics"
                        :key="topic.id"
                        class="topic"
                    >
                        <!-- Заголовок раздела -->
                        <div
                            class="topic__header"
                            :class="{ 'is-locked': !isTopicUnlocked(topic) }"
                            @click="toggleTopicSafe(topic)"
                        >
                            <span>{{ idx + 1 }}</span>
                            <h4>{{ topic.title }}</h4>
                            <span
                                class="toggle-caret"
                                :class="{ 'is-open': openTopic === topic.id }"
                            ></span>
                        </div>
                        <transition name="collapse">
                            <!-- ГЛАВЫ: карточки  -->
                            <ul
                                v-show="isTopicOpen(topic.id)"
                                class="chapters-grid"
                            >
                                <li
                                    v-for="ch in topic.chapters"
                                    :key="ch.id"
                                    class="chapter-card"
                                >
                                    <!-- управляемый клик, без прямого href -->
                                    <a
                                        class="chapter-link"
                                        href="#"
                                        @click.prevent="openChapter(topic, ch)"
                                    >
                                        <div
                                            class="chapter-card__preview-wrapper"
                                            :class="{
                                                'is-completed': ch.is_completed,
                                            }"
                                        >
                                            <div 
                                                v-show="ch.is_completed"
                                                class="good"
                                            >
                                                <img
                                                    width="15"
                                                    height="15"
                                                    src="../../img/circle.png"
                                                    alt=""
                                                />
                                            </div>
                                            <img
                                                width="30"
                                                height="50"
                                                :src="getPreviewSrc(ch)"
                                                :alt="ch.title"
                                                class="chapter-card__preview"
                                            />
                                            <span
                                                v-if="ch.is_completed"
                                                class="chapter-card__badge"
                                            >
                                                <i class="icon-check"></i>
                                            </span>
                                        </div>
                                        <h5 class="chapter-card__title">
                                            {{ ch.title }}
                                        </h5>
                                    </a>
                                </li>
                            </ul>
                        </transition>
                    </li>
                </ul>
                <!-- Блок итогового теста -->
                <!-- <div v-if="course.id" class="final-test-block">
                    <h3>Финальная работа курса «{{ course.card_title }}»</h3>
                    <p>Вы сможете пройти тест после завершения всех глав.</p>

                    <a
                        v-if="!testPassed"
                        :href="`/final-test/${course.id}`"
                        class="button"
                        :class="{ 'button--disabled': !allChaptersCompleted }"
                        :disabled="!allChaptersCompleted"
                        @click.prevent="handleFinalTestClick"
                    >
                        Пройти тест
                    </a>
                    <div v-else class="test-passed-message">
                        <div class="good__test">
                            <img
                                width="15"
                                height="15"
                                src="../../img/circle.png"
                                alt=""
                            />
                        </div>
                        <span>Вы успешно справились с итоговым тестом!</span>
                    </div>
                </div> -->
            </div>
        </div>
        <div
            v-if="showTestLockModal"
            class="modal-overlay"
            @click.self="closeTestLockModal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="test-lock-title"
        >
            <div class="modal-content modal-content--center">
                <h3 id="test-lock-title">
                    Тест станет доступен после прохождения всего материала
                </h3>
                <p style="margin-top: 0.5rem">
                    Ваш текущий прогресс:
                    <strong>{{ progressPercentage }}%</strong>. Завершите все
                    главы, чтобы открыть итоговый тест.
                </p>
                <div style="margin-top: 1rem; text-align: right">
                    <button class="button" @click="closeTestLockModal">
                        Понятно
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";
import EditorJS from "@editorjs/editorjs";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import ImageTool from "@editorjs/image";
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";

import iconText from "../../img/text.png";
import iconVideo from "../../img/video.png";
import iconTerms from "../../img/terms.png";
import iconTask from "../../img/task.png";
import iconPresentation from "../../img/presentation.png";

function goToFinalTest() {
    window.location.href = `/final-test/${course.id}`;
}

const iconMap = {
    text: iconText,
    video: iconVideo,
    terms: iconTerms,
    task: iconTask,
    presentation: iconPresentation,
};

function getPreviewSrc(chapter) {
    if (chapter.preview) return chapter.preview;
    return iconMap[chapter.type] || iconText; // text по умолчанию
}

dayjs.extend(relativeTime);
dayjs.locale("ru");

// const openTopic = ref(null);
// function toggleTopic(id) {
//   openTopic.value = openTopic.value === id ? null : id;
// }
// --- многооткрывающийся аккордеон ---
const openTopics = ref(new Set());

function isTopicOpen(id) {
    return openTopics.value.has(id);
}

function toggleTopic(id) {
    // т.к. Set не реактивен, после изменения создаём новый Set
    const s = new Set(openTopics.value);
    if (s.has(id)) s.delete(id);
    else s.add(id);
    openTopics.value = s;
}

// раскрыть все разделы (можно звать после загрузки тем)
function openAllTopicsInitially() {
    const s = new Set();
    (topics.value || []).forEach((t) => s.add(t.id));
    openTopics.value = s;
}

/* ======================================================
   1. Курс, темы, главы
===================================================== */
const course = ref(window.initialCourseData || {});
const topics = ref(course.value.topics || []);
const selectedTopic = ref(null);
const selectedChapter = ref(null);
const maxQr = ref({ image: "", link: "" });

let editorInstance = null;
const showSolution = ref(false);

/* ---------- Файлы / презентации ---------- */
const fileSrc = computed(() => {
    const ch = selectedChapter.value;
    if (!ch) return null;
    let raw =
        ch.file_path ||
        ch.attachment_path ||
        ch.presentation_path ||
        ch.file ||
        "";
    if (!raw) return null;
    if (/^https?:\/\//i.test(raw)) return raw;
    if (raw.startsWith("/storage/")) return raw;
    if (raw.startsWith("storage/")) return "/" + raw;
    return "/storage/files/" + raw;
});

const presentationSrc = computed(() => {
    const ch = selectedChapter.value;
    if (!ch) return null;
    let raw = ch.presentation_path || ch.presentation || "";
    if (/^https?:\/\//i.test(raw)) return raw;
    if (raw.startsWith("/storage/")) return raw;
    if (raw.startsWith("storage/")) return "/" + raw;
    return "/storage/presentations/" + raw;
});

const presentationExt = computed(() => {
    if (!presentationSrc.value) return "";
    return presentationSrc.value.split(".").pop().toLowerCase();
});

const embeddedSrc = computed(() => {
    if (!presentationSrc.value) return null;
    if (presentationExt.value === "pdf") return presentationSrc.value;
    if (["ppt", "pptx"].includes(presentationExt.value)) {
        return (
            "https://viewer.zoho.com/api/url?embed=true&url=" +
            encodeURIComponent(location.origin + presentationSrc.value)
        );
    }
    return (
        "https://view.officeapps.live.com/op/embed.aspx?src=" +
        encodeURIComponent(location.origin + presentationSrc.value)
    );
});

function toggleSolution() {
    showSolution.value = !showSolution.value;
}

function goBack() {
    window.location.href = "/cabinet";
}

function normalizeQrSrc(raw) {
    if (!raw) return "";
    if (raw.startsWith("http://") || raw.startsWith("https://")) return raw;
    if (raw.startsWith("data:") || raw.startsWith("blob:")) return raw;
    if (raw.startsWith("/")) return raw;
    return "/" + raw;
}

async function loadMaxQr() {
    if (!courseId.value) return;
    try {
        const { data } = await axios.get(`/api/courses/${courseId.value}/qr`);
        if (data?.qr) {
            maxQr.value = {
                image: normalizeQrSrc(data.qr.qr_image),
                link: data.qr.link || "",
            };
        } else {
            maxQr.value = { image: "", link: "" };
        }
    } catch (e) {
        console.error("Ошибка при загрузке QR-кода:", e);
        maxQr.value = { image: "", link: "" };
    }
}
function selectTopic(topic) {
    selectedTopic.value = topic;
    selectedChapter.value = null;
    destroyEditor();
}
function deselectTopic() {
    selectedTopic.value = null;
    selectedChapter.value = null;
    destroyEditor();
}
function selectChapter(chapter) {
    selectedChapter.value = chapter;
}

function goToPrevChapter() {
    if (
        !selectedTopic.value ||
        !selectedTopic.value.chapters ||
        !selectedChapter.value
    )
        return;
    const chapters = selectedTopic.value.chapters;
    const index = chapters.findIndex(
        (ch) => ch.id === selectedChapter.value.id
    );
    if (index > 0) selectChapter(chapters[index - 1]);
}

const showNextButton = ref(true);
function goToNextChapter() {
    showNextButton.value = false;
    setTimeout(() => {
        if (
            selectedTopic.value &&
            selectedTopic.value.chapters &&
            selectedChapter.value
        ) {
            const chapters = selectedTopic.value.chapters;
            const index = chapters.findIndex(
                (ch) => ch.id === selectedChapter.value.id
            );
            if (index < chapters.length - 1) selectChapter(chapters[index + 1]);
        }
        showNextButton.value = true;
    }, 500);
}

const canGoPrev = computed(() => {
    if (
        !selectedTopic.value ||
        !selectedTopic.value.chapters ||
        !selectedChapter.value
    )
        return false;
    const chapters = selectedTopic.value.chapters;
    const index = chapters.findIndex(
        (ch) => ch.id === selectedChapter.value.id
    );
    return index > 0;
});
const canGoNext = computed(() => {
    if (
        !selectedTopic.value ||
        !selectedTopic.value.chapters ||
        !selectedChapter.value
    )
        return false;
    const chapters = selectedTopic.value.chapters;
    const index = chapters.findIndex(
        (ch) => ch.id === selectedChapter.value.id
    );
    return index < chapters.length - 1;
});

/* ---------- EditorJS ---------- */
function destroyEditor() {
    if (editorInstance && typeof editorInstance.destroy === "function") {
        editorInstance.destroy();
        editorInstance = null;
    }
}
function initEditor(contentData) {
    destroyEditor();
    if (typeof contentData === "string") {
        try {
            contentData = JSON.parse(contentData);
        } catch {
            contentData = {};
        }
    }
    editorInstance = new EditorJS({
        holder: "editorjs",
        readOnly: true,
        data: contentData,
        tools: {
            header: { class: Header, inlineToolbar: ["link"] },
            list: { class: List, inlineToolbar: true },
            image: {
                class: ImageTool,
                config: {
                    endpoints: {
                        byFile: "/api/uploadFile",
                        byUrl: "/api/fetchUrl",
                    },
                },
            },
        },
    });
}

watch(selectedChapter, (newChapter) => {
    if (newChapter && newChapter.content) {
        if (
            ["text", "task", "terms", "presentation"].includes(newChapter.type)
        ) {
            initEditor(newChapter.content);
        } else if (newChapter.type === "video") {
            destroyEditor();
        } else {
            destroyEditor();
        }
    } else {
        destroyEditor();
    }
});

/* ======================================================
   2. Комментарии
===================================================== */
const courseComments = ref([]);
const newComment = ref("");
const replyTo = ref(null);
const replyComment = ref("");
const currentUserName = ref("Аноним");
const courseId = ref(null);

function formatTime(dateString) {
    return dayjs(dateString).fromNow();
}

async function loadCourseComments() {
    try {
        const { data } = await axios.get(
            `/api/courses/${courseId.value}/comments`
        );
        courseComments.value = data;
    } catch (e) {
        console.error("Ошибка при загрузке комментариев:", e);
    }
}

async function submitComment() {
    if (!newComment.value.trim()) return;
    try {
        const payload = { body: newComment.value };
        const storedUser = localStorage.getItem("user");
        if (storedUser) {
            const u = JSON.parse(storedUser);
            if (u?.id) payload.user_id = u.id;
        }
        const { data } = await axios.post(
            `/api/courses/${courseId.value}/comments`,
            payload
        );
        (courseComments.value || (courseComments.value = [])).push(data);
        newComment.value = "";
    } catch (e) {
        console.error("Ошибка при отправке комментария:", e);
    }
}

function replyToComment(comment) {
    replyTo.value = comment.id;
    replyComment.value = "";
}

async function submitReply(parentComment) {
    if (!replyComment.value.trim()) return;
    try {
        const payload = {
            body: replyComment.value,
            parent_id: parentComment.id,
        };
        const storedUser = localStorage.getItem("user");
        if (storedUser) {
            const u = JSON.parse(storedUser);
            if (u?.id) payload.user_id = u.id;
            if (u?.name) payload.user_name = u.name;
        }
        const { data } = await axios.post(
            `/api/courses/${courseId.value}/comments`,
            payload
        );
        if (!parentComment.children) parentComment.children = [];
        parentComment.children.push(data);
        replyComment.value = "";
        replyTo.value = null;
    } catch (e) {
        console.error("Ошибка при отправке ответа:", e);
    }
}
function cancelReply() {
    replyComment.value = "";
    replyTo.value = null;
}

/* ======================================================
   3. Прогресс / тест / сертификат
===================================================== */
const showTestLockModal = ref(false);

function handleFinalTestClick() {
    if (allChaptersCompleted.value) {
        // всё пройдено — идём на тест
        window.location.href = `/final-test/${course.value.id}`;
    } else {
        // не всё пройдено — показываем предупреждение
        showTestLockModal.value = true;
    }
}

function closeTestLockModal() {
    showTestLockModal.value = false;
}

const progressPercentage = computed(() => {
    let total = 0,
        done = 0;
    topics.value.forEach((t) =>
        t.chapters.forEach((ch) => {
            total++;
            if (ch.is_completed) done++;
        })
    );
    return total ? Math.round((done / total) * 100) : 0;
});
const allChaptersCompleted = computed(() => progressPercentage.value === 100);
const testPassed = ref(false);
const certificateUnlocked = computed(
    () => allChaptersCompleted.value && testPassed.value
);

function getStoredUser() {
    try {
        return JSON.parse(localStorage.getItem("user") || "{}");
    } catch {
        return {};
    }
}
const stordUser = getStoredUser();

function getCourseIdFromUrl() {
    const url = new URL(window.location.href);
    if (url.searchParams.has("course")) return url.searchParams.get("course");
    const match = url.pathname.match(/(\d+)(?!.*\d)/);
    return match ? match[1] : null;
}
const coursId = getCourseIdFromUrl();

async function downloadCertificate() {
    if (!certificateUnlocked.value) return;
    if (!stordUser.id || !stordUser.name)
        return alert("В localStorage нет данных пользователя");
    if (!coursId) return alert("Не удалось определить ID курса из URL");
    try {
        const resp = await axios.post(
            `/api/courses/${coursId}/certificate`,
            { user_id: stordUser.id, name: stordUser.name },
            { responseType: "blob" }
        );
        const blob = new Blob([resp.data], { type: "application/pdf" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `certificate_${stordUser.id}.pdf`;
        a.click();
        URL.revokeObjectURL(url);
    } catch (e) {
        console.error(e);
        alert("Ошибка при генерации сертификата");
    }
}

const storedUser = JSON.parse(localStorage.getItem("user") || "{}");
async function markChapterCompleted(chapter) {
    if (chapter.is_completed) return;
    try {
        await axios.post(`/api/chapters/${chapter.id}/complete`, {
            user_id: storedUser.id,
        });
        chapter.is_completed = true;
        topics.value.forEach((t) =>
            t.chapters.forEach((ch) => {
                if (ch.id === chapter.id) ch.is_completed = true;
            })
        );
    } catch (e) {
        console.error("Ошибка при завершении главы:", e);
    }
}

/* ======================================================
   4. Разблокировки: разделы и главы
===================================================== */
/** Индекс раздела */
function topicIndex(topic) {
    return topics.value.findIndex((t) => t.id === topic.id);
}
/** Все главы в разделе завершены? */
function isTopicFullyCompleted(topic) {
    if (!topic || !Array.isArray(topic.chapters)) return false;
    return topic.chapters.every((ch) => !!ch.is_completed);
}
/** Можно раскрыть раздел?
 *   - первый раздел — да
 *   - иначе все предыдущие разделы должны быть полностью завершены
 */
function isTopicUnlocked(topic) {
    const idx = topicIndex(topic);
    if (idx <= 0) return true;
    for (let i = 0; i < idx; i++) {
        if (!isTopicFullyCompleted(topics.value[i])) return false;
    }
    return true;
}
/** Можно открыть главу?
 *   - раздел разблокирован
 *   - все предыдущие главы в разделе завершены
 */
// function isChapterUnlocked(topic, chapter) {
//   if (!isTopicUnlocked(topic)) return false;
//   const list = topic.chapters || [];
//   const idx = list.findIndex(ch => ch.id === chapter.id);
//   if (idx <= 0) return true;
//   for (let i = 0; i < idx; i++) {
//     if (!list[i].is_completed) return false;
//   }
//   return true;
// }
/** Безопасное раскрытие раздела */
function toggleTopicSafe(topic) {
    if (!isTopicUnlocked(topic)) {
        alert("Сначала пройдите все главы в предыдущих разделах.");
        return;
    }
    toggleTopic(topic.id);
}
/** Переход к главе */
function openChapter(topic, chapter) {
    //   if (!isChapterUnlocked(topic, chapter)) {
    //     alert("Сначала завершите предыдущие главы в этом разделе.");
    //     return;
    //   }
    window.location.href = `/chapter/${chapter.id}`;
}

/* ======================================================
   5. FAQ, другие курсы
===================================================== */
const faqQuestions = ref([]);
async function loadFaqs() {
    try {
        const { data } = await axios.get("/api/faqs");
        faqQuestions.value = data.map((item) => ({ ...item, isOpen: false }));
    } catch (e) {
        console.error("Ошибка при загрузке FAQ:", e);
    }
}

const allCourses = ref([]);
const upgradeCourses = computed(() =>
    allCourses.value.filter((c) => c.upgradequalification === 1)
);
async function loadCourses() {
    try {
        const { data } = await axios.get("/api/courses");
        allCourses.value = data;
    } catch (e) {
        console.error("Ошибка при загрузке курсов:", e);
    }
}

const difficultyBgClass = {
    basic: "block-info_bg-cyan",
    middle: "block-info_bg-fiolet",
    advanced: "block-info_bg-orange",
    mixed: "block-info_bg-green",
};
const difficultyTranslation = {
    basic: "Начинающий",
    middle: "Средний",
    advanced: "Продвинутый",
    mixed: "Смешанный",
};

/* ======================================================
   6. onMounted
===================================================== */
onMounted(async () => {
    if (course.value.id) {
        courseId.value = course.value.id;
        await loadMaxQr();
    } else {
        const parts = window.location.pathname.split("/");
        const courseIdFromUrl = parts[parts.length - 1];
        courseId.value = courseIdFromUrl;
        await loadMaxQr();

        axios
            .get(`/api/course/${courseIdFromUrl}/topics`, {
                params: { user_id: storedUser.id },
            })
            .then((response) => {
                topics.value = (response.data.topics || []).sort(
                    (a, b) => new Date(a.created_at) - new Date(b.created_at)
                );
                topics.value.forEach((topic) => {
                    topic.chapters.sort(
                        (a, b) =>
                            new Date(a.created_at) - new Date(b.created_at)
                    );
                });
                topics.value.forEach((topic) => {
                    topic.chapters.forEach((chapter) => {
                        if (typeof chapter.is_completed === "undefined")
                            chapter.is_completed = false;
                    });
                });
                course.value = response.data.course || {};
            })
            .catch((error) =>
                console.error("Ошибка при загрузке тем курса:", error)
            );
    }

    loadCourseComments();
    loadFaqs();
    loadCourses();

    if (storedUser.id && courseId.value) {
        try {
            const { data } = await axios.get("/api/final-test/status", {
                params: { user_id: storedUser.id, course_id: courseId.value },
            });
            testPassed.value = !!data.passed;
        } catch (e) {
            console.warn("Не удалось получить статус теста", e);
        }
    }
});

/* ======================================================
   7. Лайки / дизлайки
===================================================== */
const userVotes = ref({});
function findCommentByIdInTree(id, arr) {
    const q = [...arr];
    while (q.length) {
        const c = q.shift();
        if (c.id === id) return c;
        if (c.children?.length) q.push(...c.children);
    }
    return null;
}
function updateLocalCommentOptimistic(id, field, delta) {
    const t = findCommentByIdInTree(id, courseComments.value || []);
    if (!t) return;
    if (typeof t[field] !== "number") t[field] = 0;
    t[field] += delta;
}
function saveUserVotes() {
    localStorage.setItem("userVotes", JSON.stringify(userVotes.value));
}
async function likeComment(comment) {
    const prev = userVotes.value[comment.id];
    if (prev === "like") {
        updateLocalCommentOptimistic(comment.id, "likes", -1);
        try {
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/unlike`
            );
            userVotes.value[comment.id] = undefined;
        } catch {
            updateLocalCommentOptimistic(comment.id, "likes", 1);
        }
    } else if (prev === "dislike") {
        updateLocalCommentOptimistic(comment.id, "dislikes", -1);
        updateLocalCommentOptimistic(comment.id, "likes", 1);
        try {
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/undislike`
            );
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/like`
            );
            userVotes.value[comment.id] = "like";
        } catch {
            updateLocalCommentOptimistic(comment.id, "dislikes", 1);
            updateLocalCommentOptimistic(comment.id, "likes", -1);
        }
    } else {
        updateLocalCommentOptimistic(comment.id, "likes", 1);
        try {
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/like`
            );
            userVotes.value[comment.id] = "like";
        } catch {
            updateLocalCommentOptimistic(comment.id, "likes", -1);
        }
    }
    saveUserVotes();
}
async function dislikeComment(comment) {
    const prev = userVotes.value[comment.id];
    if (prev === "dislike") {
        updateLocalCommentOptimistic(comment.id, "dislikes", -1);
        try {
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/undislike`
            );
            userVotes.value[comment.id] = undefined;
        } catch {
            updateLocalCommentOptimistic(comment.id, "dislikes", 1);
        }
    } else if (prev === "like") {
        updateLocalCommentOptimistic(comment.id, "likes", -1);
        updateLocalCommentOptimistic(comment.id, "dislikes", 1);
        try {
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/unlike`
            );
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/dislike`
            );
            userVotes.value[comment.id] = "dislike";
        } catch {
            updateLocalCommentOptimistic(comment.id, "likes", 1);
            updateLocalCommentOptimistic(comment.id, "dislikes", -1);
        }
    } else {
        updateLocalCommentOptimistic(comment.id, "dislikes", 1);
        try {
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/dislike`
            );
            userVotes.value[comment.id] = "dislike";
        } catch {
            updateLocalCommentOptimistic(comment.id, "dislikes", -1);
        }
    }
    saveUserVotes();
}
</script>

<style scoped>
/* стили для блокировки модуля*/
.modal-content--center {
    text-align: center;
}
.topic__header.is-locked {
    opacity: 0.6;
    cursor: not-allowed;
}
.chapter-link--locked {
    pointer-events: none;
    opacity: 0.6;
    cursor: not-allowed;
}
.chapter-card.is-locked .chapter-card__preview-wrapper {
    filter: grayscale(1);
}

.test-passed-message {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}
.test-passed-message span {
    text-align: center;
    display: block;
}
.final-test-block span {
    font-size: 12px;
    display: block;
    text-align: left;
}
.final-test-block h3 {
    margin: 16px;
}
.final-test-block {
    display: flex;
    flex-direction: column;
    margin: 2rem 0;
    padding: 15px 20px;
    background: #f5f5f5;
    border-radius: 8px;
    text-align: center;
}
.backs {
    display: flex;
    height: 20px;
    width: 100%;
    max-width: 1250px;
    margin: 0 auto;
}
.collapse-enter-active,
.collapse-leave-active {
    transition: max-height 0.7s ease-in, opacity 0.5s ease;
    overflow: hidden;
}

/* Начальное состояние (до того как элемент «въехал») */
.collapse-enter-from,
.collapse-leave-to {
    max-height: 0;
    opacity: 0;
}

/* Конечное состояние (когда полностью открыт) */
.collapse-enter-to,
.collapse-leave-from {
    max-height: 800px; /* достаточно большое, чтобы влез весь список */
    opacity: 1;
}
.button--disabled {
    background: #ccc;
    cursor: not-allowed;
}
.maincontainer {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: unset;
}
.btn-back {
    font-size: 15px;
    background: none;
    border: none;
    color: #5b4bff;
    cursor: pointer;
    margin-bottom: 20px;
}
.toggle-caret {
    /* размер стрелки */
    width: 10px;
    height: 10px;

    /* «рисуем» стрелку двумя сторонами рамки */
    border-right: 2px solid black;
    border-bottom: 2px solid black;

    /* изначально каретка смотрит вправо-вниз (закрыто) */
    transform: rotate(45deg);
    transition: transform 0.25s;
}

/* когда раздел открыт – стрелка вверх */
.toggle-caret.is-open {
    transform: rotate(-135deg); /* поворачиваем на 180° */
}
.chapters-grid {
    list-style: none;
    margin: 16px 0 0;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 24px;
}

/* сама карточка-обёртка */
.chapter-card {
    display: flex;
    flex-direction: column;
}

/* превью-контейнер */
.chapter-card__preview-wrapper {
    position: relative;
    background: #eaeaf4;
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    min-height: 150px; /* гарантируем высоту до загрузки img */
    transition: transform 0.25s;
}
.chapter-card__preview-wrapper:hover {
    transform: translateY(-4px);
}

/* превью-изображение */
.chapter-card__preview {
    margin: 0 auto;
    width: 100px;
    height: 150px;
    object-fit: cover;
    display: block;
}

/* зелёный чек сверху слева */
.chapter-card__badge {
    position: absolute;
    top: 8px;
    left: 8px;
    width: 24px;
    height: 24px;
    background: var(--success);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: #fff;
}

/* длительность внизу слева */
.chapter-card__time {
    position: absolute;
    left: 8px;
    bottom: 8px;
    font-size: 12px;
    color: #fff;
    background: rgba(0, 0, 0, 0.65);
    padding: 2px 6px;
    border-radius: 4px;
}
.chapter-link {
    text-decoration: none;
}
/* подпись под карточкой */
.chapter-card__title {
    margin: 8px 0 0;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
    line-height: 1.3;
    color: #000000;
}

.content ul li {
    margin-bottom: 15px;
}

h2 {
    text-align: left;
}
.maincontainer {
    background: #ffffff;
    color: #000000;
    min-height: 100vh;
    padding: 20px;
}
.container {
    width: 100%;
    max-width: 1240px;
    margin: 25px auto;
}
.flex {
    display: flex;
    gap: 40px;
}

/* ========== Sidebar ========== */
.sidebar {
    width: 300px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Общий класс карточки */
.card {
    width: 100%;
    max-width: 500px;
    background: #eaeaf4;
    border-radius: 12px;
    padding: 20px;
}

/* Карточка курса */
.course-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.course-card__label {
    color: #617aff;
    font-size: 12px;
    margin: 0 0 7px;
}
.course-card__title {
    color: #617aff;
    font-size: 20px;
    margin: 4px 0;
    margin: 0 0 7px;
}
.course-card__sub {
    font-size: 14px;
    color: #617aff;
}
.course-card__img {
    width: 60px;
    height: 60px;
    object-fit: contain;
}
.course-stats {
    color: #617aff;
    margin: 16px 0;
    list-style: none;
    padding: 0;
    display: flex;
    flex-direction: row;
    gap: 6px;
    font-size: 14px;
    opacity: 0.9;
    justify-content: space-around;
}

.progres {
    margin: 40px 0 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.progress-bar {
    height: 6px;
    background: #ffffff;
    border-radius: 3px;
    overflow: hidden;
}
.progress-bar span {
    display: block;
    height: 100%;
    background: #5b4bff;
    transition: width 0.4s;
}

.button--disabled {
    cursor: unset !important;
    user-select: none;
    width: 100%;
    padding: 10px;
    background: #33333380 !important;
    color: #666;
    border: none;
    border-radius: 6px;
    cursor: not-allowed;
}

/* Telegram-карточка */
.telegram-card__header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}
.telegram-card__header h3 {
    font-size: 16px;
    margin: 0;
}
.telegram-card p {
    font-size: 13px;
    line-height: 1.4;
}
.telegram-card a {
    color: #5b4bff;
    text-decoration: none;
}

/* MAX QR card */
.max-qr-card {
    text-align: center;
    background: #f1f1fb;
    color: #6f738c;
    border-radius: 16px;
}

.max-qr-card__title {
    font-size: 13px;
    line-height: 1.35;
    margin: 0 0 16px;
}

.max-qr-card__qr {
    width: 210px;
    height: 210px;
    margin: 0 auto 16px;
    padding: 10px;
    border-radius: 20px;
    background: linear-gradient(135deg, #39d5cf 0%, #6a3df7 100%);
    box-shadow: 0 12px 28px rgba(63, 54, 156, 0.2);
}

.max-qr-card__qr img {
    width: 100%;
    height: 100%;
    border-radius: 14px;
    background: #ffffff;
    object-fit: cover;
}

.max-qr-card__note {
    font-size: 12px;
    margin: 0 0 6px;
}

.max-qr-card__link {
    display: inline-block;
    color: #5b4bff;
    text-decoration: none;
    font-weight: 600;
}

/* ========== Content ========== */
.content {
    flex: 1;
}

.topics-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.topic {
    border-bottom: 1px solid #333;
    padding: 12px 0;
}
.topic__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
}
.topic__header h4 {
    margin: 0 8px;
    font-size: 16px;
}
.toggle-icon i {
    font-size: 14px;
    color: #5b4bff;
}

.chapters-list {
    list-style: none;
    padding: 8px 0 0 20px;
    margin: 0;
}
.chapter {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 0;
}
.chapter__header h5 {
    margin: 0 8px;
    font-size: 14px;
    opacity: 0.9;
}
.status-icon i {
    font-size: 16px;
    color: #38b838; /* зелёный для пройденных */
}
.status-icon .icon-play-circle {
    color: #ffa500; /* оранжевый для текущих */
}
</style>

<style scoped>
.good {
    border-radius: 10px;
    position: absolute;
    top: 10px;
    right: 10px;
}
.presentation-frame {
    width: 1000px;
    height: 60vh; /* или 60vh, как удобнее */
    border: 0;
}
.link--dow {
    display: block;
    color: #0228fd;
    cursor: pointer;
    list-style: none;
    text-decoration: none;
    margin: 15px 0 0;
    transition: 0.2s color;
}
.link--dow:hover {
    color: #677eff;
}
.button--sub {
    background: rgb(56, 184, 56) !important;
}
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    /* Центрируем содержимое по вертикали и горизонтали */
    align-items: center;
    justify-content: center;
    z-index: 999; /* Поверх всего */
}

.modal-overlay {
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

/* Блок с контентом */
.modal-content {
    position: relative;
    z-index: 10000;
    background: #fff;
    border-radius: 6px;
    padding: 20px;
    max-width: 600px;
    width: 90%;
}

.modal-content h3 {
    margin: 0 0 10px;
    font-weight: bold;
}

/* Анимация появления/исчезновения (fade) */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Пример оформления кнопки (опционально) */
.button {
    width: 100%;
    margin-top: 16px;
    padding: 8px 16px;
    background: #617aff;
    color: #fff;
    border: none;
    cursor: pointer;
    border-radius: 4px;
}
.iframe {
    width: 900px;
    max-width: 900px;
    height: 600px;
}
.button {
    border: none;
}
.button_skill-next {
    display: inline-block;
    transition: transform 0.5s ease, opacity 0.5s ease;
    transform: translateX(0);
    opacity: 1;
}
.button_skill-prev.hidden,
.button_skill-next.hidden {
    display: none; /* или visibility: hidden */
}
.button--xl {
    margin-top: 20px;
    color: #fff;
    width: 75%;
    max-width: unset;
}
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
    }
}
.fade-enter-active {
    animation: fadeIn 0.5s ease forwards;
}
.fade-leave-active {
    animation: fadeOut 0.5s ease forwards;
}
.slide-right-leave-active {
    transition: all 0.5s ease;
}

.slide-right-leave-from {
    opacity: 1;
    transform: translateX(0);
}

.slide-right-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

/* Анимация для кнопки "Вперёд" через keyframes */
@keyframes slideInRight {
    from {
        transform: translateX(50px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(50px);
        opacity: 0;
    }
}

.chapter__title {
    text-align: center;
}
.info__topic {
    text-align: center;
}
.skill__content h1 {
    margin-bottom: 40px;
    text-align: left;
    font-size: 2.1em;
    text-align: center;
}
.welcome_course {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 50vh;
}
.skill__menu-main-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
@media (max-width: 767px) {
    .btn-back {
        left: 6%;
    }
    .sidebar {
        width: 250px;
    }
    .logo_couerses {
        width: 50px;
        height: 50px;
    }
}
@media (max-width: 680px) {
    .card {
        max-width: 100%;
    }
    .sidebar {
        width: 100%;
    }
    .flex {
        justify-content: center;
        flex-direction: column;
    }
}
</style>
