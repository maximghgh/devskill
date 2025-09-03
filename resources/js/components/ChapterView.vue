<template>
    <div class="maincontainer">
        <div class="backs">
            <button @click="goBack" class="btn-back">Вернуться к модулю</button>
        </div>
        <div v-if="loading" class="loading">Загрузка…</div>

        <div v-else class="container flex py-5">
            <aside class="sidebar">
                <!-- Карточка курса -->
                <div class="card course-card">
                    <div class="course-card__header">
                        <div>
                            <div class="course-card__label">Лекция</div>
                            <div class="course-card__sub">{{ typeLabel }}:</div>
                            <h2 class="course-card__title">
                                {{ chapter.title }}
                            </h2>
                        </div>
                        <!-- <img
                            :src="'/img/logo_placeholder.png'"
                            alt="Изображение курса"
                            width="93"
                            height="92"
                            class="logo_couerses"
                        /> -->
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
            </aside>
            <div class="info__card-course">
                <h1>{{ chapter.title }}</h1>
                <div v-if="chapter.type === 'video' && embedUrl">
                    <iframe
                        class="chapter-content"
                        width="100%"
                        height="400"
                        :src="embedUrl"
                        frameborder="0"
                        allow="autoplay; fullscreen; picture-in-picture"
                        allowfullscreen
                        webkitallowfullscreen
                        mozallowfullscreen
                    ></iframe>
                </div>

                <div
                    v-if="hasContent"
                    id="editorjs"
                    class="chapter-content"
                ></div>

                <!-- Итоговый тест -->
                <!-- Итоговый тест -->
                <div v-if="quizData" class="final-quiz mb-5">
                    <form @submit.prevent="submitQuiz" class="test__course">
                        <div
                            v-for="q in quizData.questions"
                            :key="q.id"
                            class="card mb-3"
                        >
                            <div class="card-body">
                                <!-- Вопрос -->
                                <h5 class="card-title">
                                    {{ q.id }}. {{ q.prompt }}
                                </h5>

                                <!-- Варианты ответа -->
                                <div class="quiz-options mt-3">
                                    <div
                                        v-for="(opt, i) in q.options"
                                        :key="i"
                                        class="form-check"
                                    >
                                        <!-- Радио для single -->
                                        <input
                                            v-if="q.type === 'single'"
                                            class="form-check-input quiz-input"
                                            type="radio"
                                            :name="'q' + q.id"
                                            :id="'q' + q.id + '_' + i"
                                            v-model="userAnswers[q.id]"
                                            :value="i"
                                            required
                                        />
                                        <!-- Чекбокс для multiple -->
                                        <input
                                            v-else
                                            class="form-check-input quiz-input"
                                            type="checkbox"
                                            :name="'q' + q.id"
                                            :id="'q' + q.id + '_' + i"
                                            v-model="userAnswers[q.id]"
                                            :value="i"
                                        />
                                        <label
                                            class="form-check-label"
                                            :for="'q' + q.id + '_' + i"
                                        >
                                            {{ opt }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="button button--sub">
                            Проверить тест
                        </button>
                    </form>

                    <div
                        v-if="quizResult"
                        class="alert alert-info mt-4"
                        role="alert"
                    >
                        <h4 class="alert-heading">
                            Ваш результат: {{ quizResult.score }}%
                        </h4>
                        <p>
                            Правильно: {{ quizResult.correctCount }} из
                            {{ quizResult.total }}
                        </p>
                    </div>
                </div>

                <!-- Модальное окно -->
                <div
                    v-if="showModal"
                    class="modal-overlay"
                    @click.self="showModal = false"
                >
                    <div class="modal">
                        <button class="modal-close" @click="showModal = false">
                            ×
                        </button>
                        <h2>Правильный ответ</h2>
                        <p>{{ chapter.correct_answer }}</p>
                    </div>
                </div>
                <a
                    v-if="chapter.presentation_path"
                    :href="`../${chapter.presentation_path}`"
                    download
                    class="btn btn-download"
                >
                    Скачать учебный материал
                </a>
                <button
                    v-if="!chapter.is_completed"
                    @click="markChapterCompleted(chapter)"
                    class="button button--sub"
                >
                    Отметить как пройдено
                </button>
                <div v-else>
                    <p>Глава пройдена!</p>
                </div>
                <div v-if="(isTask || isTerms) && showSubmitForm" class="form">
                    <h1 class="h1__form">Проверка работы</h1>
                    <div class="task-submit-form">
                        <form @submit.prevent="submitTask">
                            <div>
                                <textarea
                                    placeholder="Написать сообщение"
                                    id="taskMessage"
                                    class="form-textarea"
                                    v-model="taskMessage"
                                    :disabled="loading"
                                    rows="3"
                                ></textarea>
                            </div>
                            <div class="form__bottom">
                                <label class="file-upload">
                                    <span class="file-upload__icon"
                                        ><svg
                                            version="1.1"
                                            id="Layer_1"
                                            xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink"
                                            x="0px"
                                            y="0px"
                                            viewBox="0 0 507.963 507.963"
                                            style="
                                                enable-background: new 0 0
                                                    507.963 507.963;
                                            "
                                            xml:space="preserve"
                                        >
                                            <g>
                                                <g>
                                                    <path
                                                        d="M464.782,25.247c-49.8-37.9-115.8-28.9-153.6,20.8l-175.5,230.5c-20.7,27.1-15.4,66,11.7,86.7 c26.7,20.3,65.4,16.2,86.7-11.7l158.9-208.6c4.7-6.2,3.5-15-2.7-19.7c-6.2-4.7-15-3.5-19.7,2.7l-158.9,208.5 c-13.7,18-35.2,15.5-47.2,6.4c-14.8-11.2-17.7-32.5-6.4-47.3l175.5-230.5c28.1-37,76-44.5,114.2-15.4 c35.8,27.2,42.7,78.5,15.5,114.2l-203.1,266.7c-46.7,53.5-125.9,66.5-181.1,24.5c-56.8-43.1-67.8-124.5-24.6-181.2l133.3-175.1 c4.7-6.2,3.5-15-2.7-19.7c-6.2-4.7-15-3.5-19.7,2.7l-133.3,175.1c-52.5,69-39.2,168.1,29.9,220.6c68.4,52,171.2,35.1,220.6-29.8 l203.1-266.7C522.182,130.747,512.882,61.847,464.782,25.247z"
                                                    />
                                                </g>
                                            </g></svg
                                    ></span>
                                    <span class="file-upload__text"
                                        >Прикрепить файл (не более 100 МБ)</span
                                    >
                                    <input type="file" hidden ref="taskFileInput" />
                                </label>
                            </div>
                            <div class="line"></div>
                            <div class="teacher__block">
                                <div class="teacher">
                                    <label class="form-label" for=""
                                        >Преподватель:</label
                                    >
                                    <img
                                        :src="'https://devskills.foncode.ru/img/nofotolk.png'"
                                        alt="Фото преподвателя"
                                        class="form__img"
                                    />
                                    <label class="form-label" for=""
                                        >Бандуков Максим Сергеевич</label
                                    >
                                </div>

                                <button
                                    type="submit"
                                    :disabled="loading"
                                    class="button button--form"
                                >
                                    Отправить на проверку
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div v-if="mySubmission" class="submission-info">
                    <!-- <p><b>Статус:</b> {{ statusLabel(mySubmission.status) }}</p> -->
                    <p v-if="mySubmission.teacher_comment"><b>Комментарий преподавателя:</b> {{ mySubmission.teacher_comment }}</p>
                    <!-- <p v-if="mySubmission.file_url"><b>Файл:</b> <a :href="mySubmission.file_url" target="_blank">Скачать</a></p> -->
                    <p class="hint">{{ submissionMessage }}</p>
                </div>
            </div>
            
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, nextTick } from "vue";
import axios from "axios";
import EditorJS from "@editorjs/editorjs";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import ImageTool from "@editorjs/image";

axios.defaults.baseURL = "/api";

/* ---------- Состояние формы / моя отправка ---------- */
const taskFileInput = ref(null);
const taskMessage   = ref("");
const mySubmission  = ref(null);   // объект отправки ИЛИ null

/* ---------- Глава / отображение контента ---------- */
const chapter   = ref({});
const loading   = ref(true);
const showModal = ref(false);
let editor = null;

const quizType  = "presentation";
const chapterId = computed(() =>
  window.location.pathname.match(/\/chapter\/(\d+)/)?.[1] || null
);
const embedUrl = computed(() => {
  const url = chapter.value.video_url;
  if (!url) return "";
  const idPart = url.match(/\/video\/([^\/\?]+)/)?.[1];
  return idPart ? `https://rutube.ru/play/embed/${idPart}?autoplay=false` : url;
});

const isTask         = computed(() => chapter.value.type === "task");
const isTerms        = computed(() => chapter.value.type === "terms");
const hasContent     = computed(() => !!chapter.value.content && chapter.value.type !== quizType);
const hasPresentation= computed(() => !!chapter.value.presentation_path);

const typeLabel = computed(() => {
  switch (chapter.value?.type) {
    case "video":        return "Видео материал";
    case "task":         return "Практическая работа";
    case "terms":        return "Лабораторная работа";
    case "presentation": return "Тест";
    default:             return chapter.value?.type || "";
  }
});

/* ---------- Тест ---------- */
const quizData    = ref(null);
const userAnswers = reactive({});
const quizResult  = ref(null);

function submitQuiz() {
  if (!quizData.value) return;
  const qs = quizData.value.questions;
  let correctCount = 0;
  qs.forEach((q) => {
    const ua = userAnswers[q.id];
    if (q.type === "single" && ua === q.answer) correctCount++;
    else if (q.type === "multiple" && Array.isArray(ua) &&
             ua.sort().toString() === q.answer.sort().toString()) correctCount++;
  });
  quizResult.value = {
    score: Math.round((correctCount / qs.length) * 100),
    correctCount,
    total: qs.length,
  };
}

/* ---------- Пользователь ---------- */
const storedUser = JSON.parse(localStorage.getItem("user") || "{}");

/* ---------- Вспомогательные ---------- */
function statusLabel(s) {
  const map = {
    pending:   "Отправлено, ожидает проверки",
    in_review: "На проверке",
    approved:  "Зачтено",
    rejected:  "Отправлено на доработку",
  };
  return map[s] ?? s;
}

/** Пустая ли «отправка». Нормализуем {}, [] и т.п. в отсутствие */
function isEmptySubmission(s) {
  if (!s) return true;                         // null/undefined/''/0/false
  if (Array.isArray(s)) return s.length === 0;
  if (typeof s === "object") return Object.keys(s).length === 0;
  return false;
}

/* Текст под статусом */
const submissionMessage = computed(() => {
  const s = mySubmission.value;
  if (!s) return "";
  if (s.status === "approved")               return "Задание успешно пройдено!";
  if (s.status === "rejected")               return "Задание отправлено на доработку.";
  if (s.status === "pending" || s.status === "in_review")
                                             return "Задание отправлено. Ожидайте проверки преподавателя.";
  return statusLabel(s.status);
});

/* Показывать ли форму (только когда отправки действительно НЕТ) */
const showSubmitForm = computed(() => isEmptySubmission(mySubmission.value) || mySubmission.value?.status === "rejected");
/* Есть ли отправка — удобно для шаблона */
const hasSubmission  = computed(() => !showSubmitForm.value);

/* ---------- API: подтянуть Мою отправку ---------- */
async function fetchMySubmission() {
  try {
    const url = `/chapter/${chapterId.value}/my-submission`;
    const { data } = await axios.get(url, { params: { user_id: storedUser.id } });

    // ЯВНО проверяем наличие ключа "submission"
    let s;
    if (data && typeof data === "object" && Object.prototype.hasOwnProperty.call(data, "submission")) {
      s = data.submission;                // может быть null — это нормально
    } else {
      s = data;                            // сервер вернул сам объект отправки
    }

    // нормализуем «пустые» значения в null
    if (isEmptySubmission(s)) s = null;

    mySubmission.value = s;
  } catch (e) {
    console.warn("Не удалось получить мою отправку:", e);
    mySubmission.value = null;
  }
}


/* ---------- API: загрузка главы ---------- */
async function fetchChapter() {
  if (!chapterId.value) {
    alert("Не удалось определить ID главы");
    return;
  }
  try {
    const { data } = await axios.get(`/chapter/${chapterId.value}`, {
      params: { user_id: storedUser.id },
    });
    chapter.value = data;

    if (chapter.value.type === quizType && chapter.value.content) {
      const raw = typeof chapter.value.content === "string"
        ? JSON.parse(chapter.value.content)
        : chapter.value.content;
      let quizBlockData = null;
      if (Array.isArray(raw.blocks)) {
        const quizBlock = raw.blocks.find((b) => b.type === "quiz");
        quizBlockData = quizBlock?.data || null;
      }
      if (quizBlockData && Array.isArray(quizBlockData.questions)) {
        quizBlockData.questions.forEach((q) => {
          if (q.type === 0 || q.type === "one") q.type = "single";
          if (q.type === 1 || q.type === "many") q.type = "multiple";
          userAnswers[q.id] = q.type === "single" ? null : [];
        });
        quizData.value = quizBlockData;
      } else {
        quizData.value = null;
      }
    }
  } catch (err) {
    console.error(err);
    alert("Не удалось загрузить главу");
    return;
  }

  loading.value = false;

  if (hasContent.value) {
    await nextTick();
    if (editor) await editor.destroy();
    editor = new EditorJS({
      holder: "editorjs",
      readOnly: true,
      tools: {
        header: { class: Header, inlineToolbar: ["link"] },
        list:   { class: List, inlineToolbar: true },
        image:  { class: ImageTool, config: { endpoints: { byFile: "/api/uploadFile", byUrl: "/api/fetchUrl" } } },
      },
      data: typeof chapter.value.content === "string"
        ? JSON.parse(chapter.value.content)
        : (chapter.value.content || {}),
    });
  }

  // после главы — тянем мою отправку
  await fetchMySubmission();
}

/* ---------- API: отправка задания ---------- */
async function submitTask() {
  if (loading.value) return;

  const userId = storedUser?.id;
  const chId   = chapter.value?.id;

  const courseId =
    chapter.value?.course_id ??
    chapter.value?.course?.id ??
    chapter.value?.topic?.course_id ??
    null;

  if (!userId) { alert("Ошибка: не определён пользователь."); return; }
  if (!chId)   { alert("Ошибка: не определён ID главы.");   return; }

  // Если задание отклонено, сбрасываем статус на "pending"
  if (mySubmission.value?.status === "rejected") {
    mySubmission.value.status = "pending";
  }

  loading.value = true;
  try {
    const formData = new FormData();

    // файл обязателен
    const fileEl = taskFileInput.value;
    if (!fileEl || !fileEl.files || fileEl.files.length === 0) {
      alert("Прикрепите файл с решением.");
      loading.value = false; return;
    }
    const f = fileEl.files[0];

    // простая валидация
    const allowed = [
      "application/pdf",
      "application/msword",
      "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
      "image/png", "image/jpeg",
      "application/zip", "application/x-zip-compressed",
      "application/x-rar-compressed", "application/x-7z-compressed",
    ];
    if (!allowed.includes(f.type)) {
      alert("Недопустимый тип файла. Разрешены: PDF, DOC/DOCX, PNG/JPG, ZIP/RAR/7Z.");
      loading.value = false; return;
    }
    const max = 100 * 1024 * 1024;
    if (f.size > max) {
      alert("Файл больше 100 МБ.");
      loading.value = false; return;
    }

    formData.append("file", f);
    formData.append("message", taskMessage.value || "");
    formData.append("chapter_id", String(chId));
    formData.append("user_id",    String(userId));
    if (courseId) formData.append("course_id", String(courseId));

    // отправка
    const { data } = await axios.post("/submitTask", formData);

    // очистка полей
    taskMessage.value = "";
    if (fileEl) fileEl.value = "";

    // Показать статус сразу и подтянуть «истинный» объект с бэка
    mySubmission.value = data?.submission || { status: "pending", comment: taskMessage.value };
    await fetchMySubmission(); // чтобы заполнить file_url и т.п.

    alert("Задание отправлено на проверку!");
  } catch (e) {
    console.error("Ошибка при отправке задания", e);
    alert("Произошла ошибка. Попробуйте снова.");
  } finally {
    loading.value = false;
  }
}


/* ---------- Прочее ---------- */
function goBack() {
  window.location.href = document.referrer || "/";
}
async function markChapterCompleted(ch) {
  if (ch.is_completed) return;
  try {
    await axios.post(`/chapters/${ch.id}/complete`, { user_id: storedUser.id });
    ch.is_completed = true;
  } catch (e) {
    console.error("Ошибка при завершении главы:", e);
  }
}

onMounted(fetchChapter);
</script>



<style scoped>

.submission-info{
    margin: 20px 0;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
    background: #5de47d75;
}

/* кнопка скачивания файла */
.btn-download {
    display: inline-block;
    padding: 12px 20px;
    background-color: #617aff;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
    margin: 20px 0;
}

.btn-download:hover {
    background-color: #4a3cbb;
    transform: translateY(-2px);
}
/* форма */
.form {
    margin: 50px 0 0;
}
.file-upload {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    color: #5f6368; /* цвет текста */
    font-size: 14px;
}

.file-upload__icon {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    font-size: 14px;
}
.file-upload__icon svg {
}
.file-upload__text {
    white-space: nowrap;
}

.file-upload:hover .file-upload__icon {
    background-color: #e0e0e0;
}

.task-submit-form {
    color: #fff;
    padding: 10px 20px 20px 20px;
    border-radius: 8px;
    border: 1px solid #617aff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    margin: 20px auto;
}

.form-label {
    color: #617aff;
    display: block;
    font-size: 14px;
    font-weight: 500;
}
.form-textarea {
    font-family: JanoSansProLight;
    width: 100%;
    max-width: 840px;
    height: 100%;
    min-height: 70px;
    resize: none;
    padding: 10px 0;
    border: 1px solid #5e5e5e00;
    border-radius: 6px;
    font-size: 14px;

    transition: border-color 0.3s ease;
}
.form-textarea::placeholder {
    color: #617aff;
}
.teacher {
    display: flex;
    align-items: center;
    gap: 10px;
}
.teacher__block {
    margin: 10px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.form__img {
    width: 30px;
    height: 30px;
}
.line {
    width: 100%;
    border-bottom: 1px solid #617aff;
    margin: 20px 0;
}
.form__bottom {
    display: flex;
    align-items: center;
    gap: 40px;
}
.h1__form {
    color: black;
    z-index: 10000;
    font-size: 26px;
    text-align: left;
}
.form-input {
    width: 100%;
    max-width: 840px;
    padding: 10px;
    border: 1px solid #5e5e5e;
    border-radius: 6px;
    background-color: #3a4046;
    color: #fff;
    font-size: 14px;
    margin-bottom: 16px;
    transition: border-color 0.3s ease;
}

.form-input:focus {
    border-color: #5b4bff;
    outline: none;
}

.button {
    padding: 12px 20px;
    background-color: #5b4bff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    width: 100%;
    margin-top: 20px;
    transition: background-color 0.3s;
}

.button:hover {
    background-color: #4a3cbb;
}

.button:disabled {
    background-color: #33333380;
    cursor: not-allowed;
}
.button--form {
    max-width: 300px;
    margin-top: 0 !important;
}
/* Картинка с куратором */
.task-submit-form .task-message {
    font-size: 14px;
    color: #ccc;
    margin-top: 20px;
}

.task-submit-form .task-message a {
    color: #5b4bff;
    text-decoration: none;
}

.task-submit-form .task-message a:hover {
    text-decoration: underline;
}

/* Описание состояния куратором */
.task-submit-form .curator-status {
    font-size: 14px;
    margin-top: 10px;
    color: #ccc;
}

.task-submit-form .curator-status .status-text {
    color: #fbbd08;
    font-weight: 600;
}

/* элементы блоков */
.backs {
    display: flex;
    height: 20px;
    width: 100%;
    max-width: 1250px;
    margin: 0 auto;
}
.maincontainer {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: unset;
}
.info__card-course {
    width: 100%;
}
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
    text-align: left;
}
.course-card__sub {
    margin: 0 0 8px;
    font-size: 14px;
    color: #617aff;
}
.course-card__img {
    width: 60px;
    height: 60px;
    object-fit: contain;
}
.test__course {
    display: flex;
    flex-direction: column;
    gap: 15px;
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
.card {
    width: 100%;
    /* max-width: 500px; */
    background: #eaeaf4;
    border-radius: 12px;
    padding: 20px;
}
.sidebar {
    width: 300px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.flex {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 40px;
}
.alert {
    display: flex;
    align-items: center;
    gap: 20px;
    margin: 20px 0;
}
.card-title {
    margin: 20px 0 20px;
}
/* ---------- КАСТОМНЫЕ РАДИО/ЧЕКБОКСЫ ---------- */
.form-check-input.quiz-input {
    appearance: none;
    -webkit-appearance: none;
    vertical-align: middle;
    cursor: pointer;
    /* одинаковый размер для radio и checkbox */
    width: 20px;
    height: 20px;
    margin-right: 10px;

    /* рамка */
    border: 2px solid #28a745;
    background: #fff;
    transition: background 0.15s ease-in-out;
}

/* круг для radio, квадрат для checkbox */
.form-check-input.quiz-input[type="radio"] {
    border-radius: 50%;
}
.form-check-input.quiz-input[type="checkbox"] {
    border-radius: 4px;
}

/* «галка» / «точка» — псевдоэлемент */
.form-check-input.quiz-input::before {
    content: "";
    display: block;
    width: 60%;
    height: 60%;
    margin: 20% auto;
    border-radius: inherit; /* круг или квадрат */
    background: transparent;
    transition: background 0.15s ease-in-out;
}

.form-check-input.quiz-input:checked::before {
    background: #28a745;
}

/* ---------- СТРОКА ВАРИАНТА ---------- */
.quiz-option-row,
.form-check {
    /* .form-check мы рендерим v-for’ом */
    padding: 6px 10px;
    border-radius: 6px;
    transition: background 0.15s;
}

.quiz-option-row:hover,
.form-check:hover {
    background: #f2f2f2;
}

/* подсветка выбранного */
.form-check-input.quiz-input:checked ~ .form-check-label {
    font-weight: 600;
}
.form-check-input.quiz-input:checked
  + .form-check-label,                  /* radio */
.form-check-input.quiz-input:checked
  ~ .form-check-label {
    /* checkbox */
    background: #e6f8ec;
    border-radius: 4px;
    padding: 4px 6px;
}

/* убрать дефолтный outline при фокусе */
.form-check-input.quiz-input:focus {
    outline: none;
    box-shadow: none;
}

.final-quiz {
    padding: 24px;
    background: #f8f9fa;
    border-radius: 8px;
}
.quiz-options .form-check {
    margin-bottom: 0.75rem;
}

.button {
    margin-top: 16px;
    padding: 8px 16px;
    background: #617aff;
    color: #fff;
    border: none;
    cursor: pointer;
    border-radius: 4px;
}
.button--sub {
    background: rgb(56, 184, 56) !important;
}
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}
.modal {
    background: #fff;
    padding: 24px;
    border-radius: 8px;
    max-width: 90%;
    width: 400px;
    position: relative;
}
.modal-close {
    position: absolute;
    top: 8px;
    right: 12px;
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
}
.answer {
    margin-top: 16px;
    padding: 8px 12px;
    background-color: #5b4bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.container {
    width: 100%;
    max-width: 1240px;
    margin: 25px auto;
}
.btn-back {
    font-size: 15px;
    background: none;
    border: none;
    color: #5b4bff;
    cursor: pointer;
    margin-bottom: 20px;
}
.chapter-video {
    width: 100%;
    border-radius: 8px;
    margin-bottom: 24px;
}
.chapter-content {
    margin-top: 10px;
    padding: 16px;
    margin-bottom: 24px;
}
.chapter-presentation {
    width: 100%;
    height: 70vh;
    border-radius: 8px;
    margin-bottom: 24px;
}
.loading {
    text-align: center;
    padding: 40px 0;
}
</style>
