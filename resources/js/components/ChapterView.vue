<template>
    <div v-if="loading" class="loading">Загрузка…</div>

    <div v-else class="container py-5">
        <div class="backs">
            <button @click="goBack" class="btn-back">Вернуться к курсу</button>
        </div>
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

        <div v-if="hasContent" id="editorjs" class="chapter-content"></div>

        <div v-if="isTask" class="task-block">
            <button @click="showModal = true" class="answer">
                Показать правильный ответ
            </button>
        </div>

        <!-- Итоговый тест -->
        <!-- Итоговый тест -->
        <div v-if="quizData" class="final-quiz mb-5">
            <form @submit.prevent="submitQuiz">
                <div
                    v-for="q in quizData.questions"
                    :key="q.id"
                    class="card mb-3"
                >
                    <div class="card-body">
                        <!-- Вопрос -->
                        <h5 class="card-title">{{ q.id }}. {{ q.prompt }}</h5>

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

            <div v-if="quizResult" class="alert alert-info mt-4" role="alert">
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

        <a
            v-if="chapter.presentation_path"
            :href="presentationUrl"
            download
            class="btn btn-download"
        >
            Скачать презентацию
        </a>
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

// props
const props = defineProps({ initId: Number });

// state
const chapter = ref({});
const loading = ref(true);
const showModal = ref(false);
let editor = null;

// когда тип теста совпадает с этим значением, вместо редактора показываем форму
const quizType = "presentation"; // или "final_test", как у вас настроено

// id главы
const chapterId = computed(
    () => window.location.pathname.match(/\/chapter\/(\d+)/)?.[1] || null
);
const embedUrl = computed(() => {
    // пример rutube URL из API: https://rutube.ru/video/11063d3d7e…/?r=wd
    const url = chapter.value.video_url;
    if (!url) return "";
    // Переводим "/video/ID…/" → "/play/embed/ID…?autoplay=false"
    const idPart = url.match(/\/video\/([^\/\?]+)/)?.[1];
    if (!idPart) return url;
    return `https://rutube.ru/play/embed/${idPart}?autoplay=false`;
});
// хелперы
const isTask = computed(() => chapter.value.type === "task");
const hasContent = computed(
    () => !!chapter.value.content && chapter.value.type !== quizType
);
const hasPresentation = computed(() => !!chapter.value.presentation_path);

// анализ JSON теста
const quizData = ref(null);

// ответы пользователя
// single -> число, multiple -> массив
const userAnswers = reactive({});

// результат
const quizResult = ref(null);

function submitQuiz() {
    if (!quizData.value) return;
    const qs = quizData.value.questions;
    let correctCount = 0;
    qs.forEach((q) => {
        const ua = userAnswers[q.id];
        if (q.type === "single" && ua === q.answer) correctCount++;
        else if (
            q.type === "multiple" &&
            Array.isArray(ua) &&
            ua.sort().toString() === q.answer.sort().toString()
        )
            correctCount++;
    });
    quizResult.value = {
        score: Math.round((correctCount / qs.length) * 100),
        correctCount,
        total: qs.length,
    };
}

function goBack() {
    window.location.href = document.referrer || "/";
}

const storedUser = JSON.parse(localStorage.getItem("user") || "{}");

async function fetchChapter() {
    if (!chapterId.value) {
      alert("Не удалось определить ID главы");
      return;
    }

    try {
      const { data } = await axios.get(`/chapter/${chapterId.value}`, {
        params: { user_id: storedUser.id },
      });
      console.log('[fetchChapter] raw chapter.value:', data);
      chapter.value = data;

      // только для "presentation" тестов
      if (chapter.value.type === quizType && chapter.value.content) {
        // 1) Сперва распарсим JSON
        const raw = typeof chapter.value.content === 'string'
          ? JSON.parse(chapter.value.content)
          : chapter.value.content;
        console.log('[fetchChapter] parsed raw:', raw);

        // 2) Если пришёл Editor.js-формат (с массивом blocks) — найдём блок quiz
        let quizBlockData = null;
        if (Array.isArray(raw.blocks)) {
          console.log('[fetchChapter] all blocks:', raw.blocks);
          const quizBlock = raw.blocks.find(b => b.type === 'quiz');
          console.log('[fetchChapter] quizBlock:', quizBlock);
          quizBlockData = quizBlock?.data || null;
        }

        // 3) Если после этого нет вопросов — выходим
        if (!quizBlockData || !Array.isArray(quizBlockData.questions)) {
          console.warn('[fetchChapter] нет вопросов, тест не инициализируется');
          quizData.value = null;
        } else {
          // 4) Нормализуем типы и инициализируем ответы
          quizBlockData.questions.forEach(q => {
            if (q.type === 0 || q.type === 'one')   q.type = 'single';
            if (q.type === 1 || q.type === 'many')  q.type = 'multiple';
            userAnswers[q.id] = q.type === 'single' ? null : [];
          });
          console.log('[fetchChapter] normalized questions:', quizBlockData.questions);

          // 5) Записываем в реактивную переменную
          quizData.value = quizBlockData;
          console.log('[fetchChapter] final quizData:', quizData.value);
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
          holder: 'editorjs',
          readOnly: true,
          tools: {
            header: { class: Header, inlineToolbar: ['link'] },
            list:   { class: List,   inlineToolbar: true },
            image:  {
              class: ImageTool,
              config: {
                endpoints: { byFile: '/api/uploadFile', byUrl: '/api/fetchUrl' }
              }
            }
          },
          data: typeof chapter.value.content === 'string'
                  ? JSON.parse(chapter.value.content)
                  : chapter.value.content || {}
        });
      }
}
async function markChapterCompleted(ch) {
    if (ch.is_completed) return;
    try {
        await axios.post(`/chapters/${ch.id}/complete`, {
            user_id: storedUser.id,
        });
        ch.is_completed = true;
        console.log(`Глава ${ch.id} отмечена как пройденная`);
    } catch (error) {
        console.error("Ошибка при завершении главы:", error);
    }
}

onMounted(fetchChapter);
</script>

<style scoped>
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
    max-width: 800px;
    margin: 40px auto;
    padding: 0 16px;
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
