<template>
    <div class="maincontainer">
        <div class="backs">
            <button @click="goBack" class="btn-back">Вернуться в личный кабинет</button>
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

                    <button
                        class="button"
                        :class="{ 'button--disabled': !certificateUnlocked }"
                        :disabled="!certificateUnlocked"
                        @click="downloadCertificate"
                    >
                        Сертификат
                    </button>
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
                            @click="toggleTopic(topic.id)"
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
                                v-show="openTopic === topic.id"
                                class="chapters-grid"
                            >
                                <li
                                    v-for="ch in topic.chapters"
                                    :key="ch.id"
                                    class="chapter-card"
                                >
                                    <a
                                        class="chapter-link"
                                        :href="`/chapter/${ch.id}`"
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
                                            {{ topic.id }}.{{ ch.id }}
                                            {{ ch.title }}
                                        </h5>
                                    </a>
                                </li>
                            </ul>
                        </transition>
                    </li>
                </ul>
                <!-- Блок итогового теста -->
                <div v-if="course.id" class="final-test-block">
                    <h3>Финальная работа курса «{{ course.card_title }}»</h3>
                    <p>Вы сможете пройти тест после завершения всех глав.</p>
                    <a
  v-if="course.id"
  :href="`/final-test/${course.id}`"
  class="button"
>
  Пройти тест
</a>
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
  // ведёт на маршрут, который мы создали выше
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
    // если у главы есть собственный preview, используем его
    if (chapter.preview) return chapter.preview;
    // иначе берём из маппинга по типу
    return iconMap[chapter.type] || iconText; // text по умолчанию
}

dayjs.extend(relativeTime);
dayjs.locale("ru");

const openTopic = ref(null);

function toggleTopic(id) {
    openTopic.value = openTopic.value === id ? null : id;
}

/* ======================================================
   1. Работа с курсом, темами и главами
===================================================== */
const course = ref(window.initialCourseData || {});
const topics = ref(course.value.topics || []);
const selectedTopic = ref(null);
const selectedChapter = ref(null);

let editorInstance = null;

const showSolution = ref(false);

//работа с файлами
const fileSrc = computed(() => {
    const ch = selectedChapter.value;
    if (!ch) return null;

    // возможные поля, где хранится путь
    let raw =
        ch.file_path ||
        ch.attachment_path ||
        ch.presentation_path ||
        ch.file ||
        "";

    if (!raw) return null;

    // ① абсолютный URL
    if (/^https?:\/\//i.test(raw)) return raw;

    // ② начинается с /storage/
    if (raw.startsWith("/storage/")) return raw;

    // ③ начинается с storage/ (без слэша)
    if (raw.startsWith("storage/")) return "/" + raw;

    // ④ только имя файла
    return "/storage/files/" + raw; // ← папку подберите под себя
});
const presentationSrc = computed(() => {
    const ch = selectedChapter.value;
    if (!ch) return null;

    let raw = ch.presentation_path || ch.presentation || ""; // все варианты

    // ① Если бекенд вернул абсолютный URL («https://…») — ничего не делаем
    if (/^https?:\/\//i.test(raw)) {
        return raw;
    }

    // ② Если уже начинается с «/storage/» — оставляем как есть
    if (raw.startsWith("/storage/")) {
        return raw;
    }

    // ③ Если без ведущего слэша («storage/…») — добавляем «/»
    if (raw.startsWith("storage/")) {
        return "/" + raw; // → «/storage/…»
    }

    // ④ Если пришло только имя файла («my.pptx») или подпапка+имя
    //    решаем, где у нас хранятся презентации
    return "/storage/presentations/" + raw;
});

const presentationExt = computed(() => {
    if (!presentationSrc.value) return "";
    // ".pptx" или ".pdf" → "pptx" / "pdf"
    return presentationSrc.value.split(".").pop().toLowerCase();
});

const embeddedSrc = computed(() => {
    if (!presentationSrc.value) return null;

    // 1) PDF оставляем как есть
    if (presentationExt.value === "pdf") {
        return presentationSrc.value;
    }

    // 2) PPT / PPTX → Google viewer
    if (["ppt", "pptx"].includes(presentationExt.value)) {
        return (
            "https://viewer.zoho.com/api/url?embed=true&url=" +
            encodeURIComponent(location.origin + presentationSrc.value)
        );
        // location.origin нужен, если путь относительный (/storage/…)
    }

    // 3) Фолбэк – Office viewer (DOCX, XLSX и т.п.)
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
function selectTopic(topic) {
    console.log("Выбрали тему:", topic);
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
    console.log("Выбрали главу:", chapter);
    selectedChapter.value = chapter;
}

function goToPrevChapter() {
    if (
        selectedTopic.value &&
        selectedTopic.value.chapters &&
        selectedChapter.value
    ) {
        const chapters = selectedTopic.value.chapters;
        const index = chapters.findIndex(
            (ch) => ch.id === selectedChapter.value.id
        );
        if (index > 0) selectChapter(chapters[index - 1]);
    }
}

const showNextButton = ref(true);

function goToNextChapter() {
    // Сначала убираем кнопку плавно
    showNextButton.value = false;
    // Затем через задержку выполняем переход (примерно длительность анимации)
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
        // Опционально вернуть кнопку обратно, если она нужна для следующей главы
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

function destroyEditor() {
    if (editorInstance && typeof editorInstance.destroy === "function") {
        console.log("Уничтожаем предыдущий экземпляр Editor.js");
        editorInstance.destroy();
        editorInstance = null;
    }
}

function initEditor(contentData) {
    console.log("initEditor: пришли данные контента:", contentData);
    destroyEditor();
    if (typeof contentData === "string") {
        try {
            contentData = JSON.parse(contentData);
            console.log("initEditor: успешно распарсили JSON:", contentData);
        } catch (error) {
            console.error("Ошибка парсинга JSON контента главы:", error);
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
    console.log("EditorJS инициализирован в режиме read-only.");
}

watch(selectedChapter, (newChapter) => {
    console.log("watch selectedChapter -> newChapter:", newChapter);
    if (newChapter && newChapter.content) {
        console.log(
            "Тип главы:",
            newChapter.type,
            "Содержимое content:",
            newChapter.content
        );
        if (
            ["text", "task", "terms", "presentation"].includes(newChapter.type)
        ) {
            initEditor(newChapter.content);
        } else if (newChapter.type === "video") {
            console.log(
                "Глава с видео. Editor.js не нужен, уничтожаем экземпляр."
            );
            destroyEditor();
        } else {
            console.warn(
                "Неизвестный тип главы:",
                newChapter.type,
                "— уничтожаем Editor.js."
            );
            destroyEditor();
        }
    } else {
        console.log(
            "Глава не выбрана или нет поля content. Уничтожаем Editor.js."
        );
        destroyEditor();
    }
});

/* ======================================================
   2. Комментарии для курса
===================================================== */
const courseComments = ref([]);
const newComment = ref("");
const replyTo = ref(null);
const replyComment = ref("");
const currentUserName = ref("Аноним");

// Делаем courseId реактивной переменной
const courseId = ref(null);

function formatTime(dateString) {
    return dayjs(dateString).fromNow();
}

async function loadCourseComments() {
    try {
        const response = await axios.get(
            `/api/courses/${courseId.value}/comments`
        );
        courseComments.value = response.data;
    } catch (error) {
        console.error("Ошибка при загрузке комментариев:", error);
    }
}

async function submitComment() {
    if (!newComment.value.trim()) return;
    try {
        // Формируем payload для комментария
        const payload = { body: newComment.value };

        // Извлекаем данные пользователя из localStorage, если они есть
        const storedUser = localStorage.getItem("user");
        if (storedUser) {
            const parsedUser = JSON.parse(storedUser);
            if (parsedUser && parsedUser.id) {
                payload.user_id = parsedUser.id;
            }
        }
        const response = await axios.post(
            `/api/courses/${courseId.value}/comments`,
            payload
        );
        if (courseComments.value) {
            courseComments.value.push(response.data);
        } else {
            courseComments.value = [response.data];
        }
        newComment.value = "";
    } catch (error) {
        console.error("Ошибка при отправке комментария:", error);
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
        // Также добавляем данные пользователя, если они есть
        const storedUser = localStorage.getItem("user");
        if (storedUser) {
            const parsedUser = JSON.parse(storedUser);
            if (parsedUser && parsedUser.id) {
                payload.user_id = parsedUser.id;
            }
            if (parsedUser && parsedUser.name) {
                payload.user_name = parsedUser.name;
            }
        }
        const response = await axios.post(
            `/api/courses/${courseId.value}/comments`,
            payload
        );
        if (!parentComment.children) {
            parentComment.children = [];
        }
        parentComment.children.push(response.data);
        replyComment.value = "";
        replyTo.value = null;
    } catch (error) {
        console.error("Ошибка при отправке ответа:", error);
    }
}

function cancelReply() {
    replyComment.value = "";
    replyTo.value = null;
}
//прогресс
const progressPercentage = computed(() => {
  let totalPoints    = 0;
  let completedPoints = 0;

  topics.value.forEach(topic => {
    topic.chapters?.forEach(ch => {
      const pts = Number(ch.points) || 0;   // защита от null
      totalPoints += pts;
      if (ch.is_completed) completedPoints += pts;
    });
  });

  return totalPoints
    ? Math.round((completedPoints / totalPoints) * 100)
    : 0;
});

const certificateUnlocked = computed(() => progressPercentage.value === 100);

// 2) Идентификатор курса — либо из route params, либо из localStorage

/* -------------------------------------------------
   2. Достаём пользователя из localStorage
   localStorage.setItem('user', JSON.stringify({id: 2, name: 'Иванов Иван'}))
--------------------------------------------------*/
function getStoredUser() {
    try {
        return JSON.parse(localStorage.getItem("user") || "{}");
    } catch (e) {
        console.error("Не смог распарсить user из localStorage", e);
        return {};
    }
}
const stordUser = getStoredUser();

/* -------------------------------------------------
   3. Получаем id курса из URL
      – ?course=42         -> 42
      – /courses/42        -> 42
--------------------------------------------------*/
function getCourseIdFromUrl() {
    const url = new URL(window.location.href);

    // 3.1 сначала ищем query-param
    if (url.searchParams.has("course")) {
        return url.searchParams.get("course");
    }

    // 3.2 иначе берём последнюю цифру в pathname
    const match = url.pathname.match(/(\d+)(?!.*\d)/);
    return match ? match[1] : null;
}
const coursId = getCourseIdFromUrl();

/* -------------------------------------------------
   4. Клик по кнопке = обращение к API и скачивание
--------------------------------------------------*/
async function downloadCertificate() {
    if (!certificateUnlocked) return;

    if (!stordUser.id || !stordUser.name) {
        return alert("В localStorage нет данных пользователя");
    }
    if (!coursId) {
        return alert("Не удалось определить ID курса из URL");
    }

    try {
        const response = await axios.post(
            `/api/courses/${coursId}/certificate`,
            {
                user_id: stordUser.id,
                name: stordUser.name,
                // Передавать название курса не нужно —
                // бэкенд найдёт его по id. Если хотите —
                // передайте отдельным полем.
            },
            { responseType: "blob" }
        );

        // Сохранить PDF
        const blob = new Blob([response.data], { type: "application/pdf" });
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

const storedUser = JSON.parse(localStorage.getItem("user"));
async function markChapterCompleted(chapter) {
    if (chapter.is_completed) return;
    try {
        await axios.post(`/api/chapters/${chapter.id}/complete`, {
            user_id: storedUser.id,
        });

        // 1. Вариант: прямо изменить у выбранной главы
        chapter.is_completed = true;

        // 2. Дополнительно найти эту главу внутри topics.value и проставить is_completed = true,
        //    чтобы, если пользователь переключится между главами, данные были согласованы.
        topics.value.forEach((topic) => {
            topic.chapters.forEach((ch) => {
                if (ch.id === chapter.id) {
                    ch.is_completed = true;
                }
            });
        });

        console.log(`Глава ${chapter.id} отмечена как пройденная`);
        console.log("После отметки: is_completed =", chapter.is_completed);
    } catch (error) {
        console.error("Ошибка при завершении главы:", error);
    }
}

/* ======================================================
   3. Прочие данные: FAQ, курсы для повышения квалификации и маппинг сложности
===================================================== */
const faqQuestions = ref([]);
async function loadFaqs() {
    try {
        const response = await axios.get("/api/faqs");
        console.log("Полученные FAQ:", response.data);
        faqQuestions.value = response.data.map((item) => ({
            ...item,
            isOpen: false,
        }));
    } catch (error) {
        console.error("Ошибка при загрузке FAQ:", error);
    }
}

const allCourses = ref([]);
const upgradeCourses = computed(() => {
    return allCourses.value.filter((c) => c.upgradequalification === 1);
});
async function loadCourses() {
    try {
        const response = await axios.get("/api/courses");
        allCourses.value = response.data;
    } catch (error) {
        console.error("Ошибка при загрузке курсов:", error);
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
   4. onMounted – загрузка всех данных
===================================================== */
onMounted(() => {
    // Если course.value.id уже есть, используем его:
    if (course.value.id) {
        courseId.value = course.value.id;
        console.log("Курс уже есть, id =", courseId.value);
    } else {
        // Если нет, пытаемся извлечь из URL
        const parts = window.location.pathname.split("/");
        const courseIdFromUrl = parts[parts.length - 1];
        console.log("onMounted: извлекли courseId:", courseIdFromUrl);
        courseId.value = courseIdFromUrl; // при необходимости можно parseInt

        // Дополнительно подгружаем темы
        axios
            .get(`/api/course/${courseIdFromUrl}/topics`, {
                params: { user_id: storedUser.id },
            })
            .then((response) => {
                topics.value = (response.data.topics || []).sort((a, b) => {
                    // Сортируем сами темы
                    return new Date(a.created_at) - new Date(b.created_at);
                });

                // Теперь сортируем главы в каждой теме
                topics.value.forEach((topic) => {
                    topic.chapters.sort((a, b) => {
                        return new Date(a.created_at) - new Date(b.created_at);
                    });
                });

                // Гарантируем, что каждая глава имеет свойство is_completed
                topics.value.forEach((topic) => {
                    topic.chapters.forEach((chapter) => {
                        if (typeof chapter.is_completed === "undefined") {
                            chapter.is_completed = false;
                        }
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

});

/* ======================================================
   5. Лайки и дизлайки
===================================================== */
// userVotes хранит текущее состояние голосов пользователя
const userVotes = ref({});

// Поиск комментария по ID в дереве при помощи BFS (без рекурсии).
function findCommentByIdInTree(commentId, commentsArray) {
    const queue = [...commentsArray];
    while (queue.length > 0) {
        const comment = queue.shift();
        if (comment.id === commentId) {
            return comment;
        }
        if (comment.children && comment.children.length > 0) {
            queue.push(...comment.children);
        }
    }
    return null;
}

// Функция для оптимистичного обновления поля (likes или dislikes) в одном комментарии.
function updateLocalCommentOptimistic(commentId, field, delta) {
    const target = findCommentByIdInTree(commentId, courseComments.value);
    if (target) {
        if (typeof target[field] !== "number") {
            target[field] = 0;
        }
        target[field] += delta;
        console.log(
            `Комментарий ${commentId}: поле ${field} изменено на ${delta}, теперь = ${target[field]}`
        );
    }
}

// Функция для сохранения голосов в localStorage
function saveUserVotes() {
    localStorage.setItem("userVotes", JSON.stringify(userVotes.value));
}

// Функция для установки лайка
async function likeComment(comment) {
    const prevVote = userVotes.value[comment.id];

    // Если уже стоит "like", то отменяем
    if (prevVote === "like") {
        updateLocalCommentOptimistic(comment.id, "likes", -1);
        try {
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/unlike`
            );
            userVotes.value[comment.id] = undefined;
            console.log("Лайк отменён");
        } catch (error) {
            console.error("Ошибка при отмене лайка:", error);
            updateLocalCommentOptimistic(comment.id, "likes", 1); // откат
        }
    }
    // Если стоит "dislike", снимаем дизлайк и ставим лайк
    else if (prevVote === "dislike") {
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
            console.log("Переключили с дизлайка на лайк");
        } catch (error) {
            console.error("Ошибка при переключении с дизлайка на лайк:", error);
            // откат
            updateLocalCommentOptimistic(comment.id, "dislikes", 1);
            updateLocalCommentOptimistic(comment.id, "likes", -1);
        }
    }
    // Если голос не был поставлен, ставим лайк
    else {
        updateLocalCommentOptimistic(comment.id, "likes", 1);
        try {
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/like`
            );
            userVotes.value[comment.id] = "like";
            console.log("Лайк поставлен");
        } catch (error) {
            console.error("Ошибка при лайке комментария:", error);
            updateLocalCommentOptimistic(comment.id, "likes", -1); // откат
        }
    }
    saveUserVotes();
}

// Функция для установки дизлайка
async function dislikeComment(comment) {
    const prevVote = userVotes.value[comment.id];

    // Если уже стоит "dislike", то отменяем
    if (prevVote === "dislike") {
        updateLocalCommentOptimistic(comment.id, "dislikes", -1);
        try {
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/undislike`
            );
            userVotes.value[comment.id] = undefined;
            console.log("Дизлайк отменён");
        } catch (error) {
            console.error("Ошибка при отмене дизлайка:", error);
            updateLocalCommentOptimistic(comment.id, "dislikes", 1); // откат
        }
    }
    // Если стоит "like", то снимаем лайк и ставим дизлайк
    else if (prevVote === "like") {
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
            console.log("Переключили с лайка на дизлайк");
        } catch (error) {
            console.error("Ошибка при переключении с лайка на дизлайк:", error);
            // откат
            updateLocalCommentOptimistic(comment.id, "likes", 1);
            updateLocalCommentOptimistic(comment.id, "dislikes", -1);
        }
    }
    // Если голос не был поставлен, просто ставим дизлайк
    else {
        updateLocalCommentOptimistic(comment.id, "dislikes", 1);
        try {
            await axios.post(
                `/api/courses/${courseId.value}/comments/${comment.id}/dislike`
            );
            userVotes.value[comment.id] = "dislike";
            console.log("Дизлайк поставлен");
        } catch (error) {
            console.error("Ошибка при дизлайке комментария:", error);
            updateLocalCommentOptimistic(comment.id, "dislikes", -1); // откат
        }
    }
    saveUserVotes();
    console.log(selectedChapter);
}
</script>

<style scoped>
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
.backs{
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
    position: absolute;
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
@media (max-width: 767px){
    .btn-back{
        left: 6%;
    }
    .sidebar{
        width: 250px;
    }
    .logo_couerses{
        width: 50px;
        height: 50px;
    }
}
@media (max-width: 680px){
    .card{
        max-width: 100%;
    }
    .sidebar{
        width: 100%;
    }
    .flex{
        justify-content: center;
        flex-direction: column;
    }
}
</style>
