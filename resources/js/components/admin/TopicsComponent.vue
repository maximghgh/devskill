<template>
    <div>
        <!-- Отображение информации о теме -->
        <div class="block__info">
            <a class="span__sctrelca" href="#" @click.prevent="goBack">🠔</a>
            <h1>Тема: {{ topic.title }}</h1>
        </div>

        <h2>Список глав</h2>

        <!-- Таблица с главами -->
        <div v-if="chapters.length">
            <table class="light-push-table" v-if="chapters.length">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Название</th>
                        <th>Тип</th>
                        <th>Изменения</th>
                        <th>Удалить</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(chapter, index) in paginatedChapters"
                        :key="chapter.id"
                    >
                        <td>{{ index + 1 }}</td>
                        <td>{{ chapter.title }}</td>
                        <td>{{ chapter.type }}</td>
                        <td>
                            <!-- Вместо прямого редактирования вызываем модальное окно -->
                            <button
                                class="btn__user--edit"
                                @click="openEditModal(chapter)"
                            >
                                Редактировать
                            </button>
                        </td>
                        <td>
                            <button
                                class="btn__user--delete"
                                @click="deleteChapter(chapter.id)"
                            >
                                Удалить
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else>
            <p class="p__topic">Пока нет глав</p>
        </div>
        <div class="pagination-users" v-if="totalPagesChapters > 1">
            <button
                :disabled="currentPageChapters === 1"
                @click="currentPageChapters--"
            >
                ‹ Назад
            </button>

            <button
                v-for="p in totalPagesChapters"
                :key="p"
                :class="{ active: currentPageChapters === p }"
                @click="currentPageChapters = p"
            >
                {{ p }}
            </button>

            <button
                :disabled="currentPageChapters === totalPagesChapters"
                @click="currentPageChapters++"
            >
                Вперёд ›
            </button>
        </div>
        <div v-if="editModalOpen" class="modal-overlay">
            <div class="modal-content">
                <h3>Редактирование главы</h3>
                <div class="edit-course-form-container">
                    <form
                        @submit.prevent="saveEditedChapter"
                        class="edit-course-form"
                    >
                        <!-- Поле "Название" -->
                        <div class="form-group">
                            <label class="form-label">Название</label>
                            <input
                                v-model="editingChapter.title"
                                type="text"
                                placeholder="Введите название главы"
                                class="form-input"
                                required
                            />
                        </div>
                        <div class="form-group form--hidden">
                            <label class="form-label">Баллы за главу</label>
                            <input
                                type="hidden"
                                v-model.number="editingChapter.points"
                                min="0"
                                class="form-input"
                            />
                        </div>
                        <!-- Поле "Тип" -->
                        <div class="form-group">
                            <label class="form-label">Тип</label>
                            <select
                                v-model="editingChapter.type"
                                class="form-input"
                            >
                                <option value="video">Видео</option>
                                <option value="text">Текст</option>
                                <option value="task">Задания</option>
                                <option value="terms">Термины</option>
                                <option value="presentation">
                                    Итоговый тест
                                </option>
                            </select>
                        </div>

                        <!-- Поле для ссылки (только для видео) -->
                        <div
                            v-if="editingChapter.type === 'video'"
                            class="form-group"
                        >
                            <label class="form-label">Ссылка на видео</label>
                            <input
                                v-model="editingChapter.video_url"
                                type="text"
                                placeholder="https://..."
                                class="form-input"
                            />
                        </div>

                        <!-- Поле для правильного ответа (только для заданий) -->
                        <div
                            v-if="editingChapter.type === 'task'"
                            class="form-group"
                        >
                            <label class="form-label">Правильный ответ</label>
                            <textarea
                                v-model="editingChapter.correct_answer"
                                placeholder="Введите правильный ответ"
                                class="form-textarea form-textarea--xl"
                            ></textarea>
                        </div>

                        <!-- Редактор контента Editor.js -->
                        <div class="form-group">
                            <label class="form-label">Контент</label>
                            <div
                                id="editor-edit"
                                class="editor-container"
                            ></div>
                        </div>

                        <div class="modal-buttons">
                            <button type="submit" class="form-button">
                                Сохранить
                            </button>
                            <button
                                type="button"
                                @click="closeEditModal"
                                class="form-button form-button--close"
                            >
                                Отмена
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Блок для добавления новой главы -->
        <h2>Добавить новую главу</h2>

        <!-- Выбор типа главы -->
        <div class="chapter-type-selector">
            <p class="form-label">Выберите тип главы:</p>
            <div class="button-group">
                <button @click="selectType('video')" class="type-button">
                    Видео материал
                </button>
                <button @click="selectType('text')" class="type-button">
                    Текстовый материал
                </button>
                <button @click="selectType('task')" class="type-button">
                    Практическая работа
                </button>
                <button @click="selectType('terms')" class="type-button">
                    Лабораторная работа
                </button>
                <button @click="selectType('presentation')" class="type-button">
                    Тестирование
                </button>
            </div>
        </div>

        <!-- Форма, которая появляется после выбора типа -->
        <div v-if="selectedType" class="form">
            <h3 class="h3__topic">Добавление главы</h3>
            <form @submit.prevent="submitChapter" class="chapter-form">
                <!-- Общее поле для названия главы -->
                <div class="form-group">
                    <label class="form-label">Название главы :</label>
                    <input
                        placeholder="Название главы"
                        type="text"
                        v-model="newChapter.title"
                        required
                        class="form-input"
                    />
                </div>

                <!-- Баллы -->
                <!-- <div class="form-group">
                    <label class="form-label form--hidden">Баллы за главу</label>
                    <input
                        type="hidden"
                        v-model.number="newChapter.points"
                        min="0"
                        class="form-input"
                        required
                    />
                </div> -->
                <!-- Для видео: поле для ссылки и контейнер для редактора -->
                <div v-if="selectedType === 'video'" class="form-group">
                    <div class="form-group form-group--margin">
                        <label class="form-label">Ссылка на видео:</label>
                        <input
                            placeholder="Ссылка на видео"
                            type="text"
                            v-model="newChapter.video_url"
                            class="form-input"
                        />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Описание темы :</label>
                        <div id="editor-video" class="editor-container"></div>
                    </div>
                </div>

                <!-- Для текстового материала -->
                <div v-else-if="selectedType === 'text'" class="form-group">
                    <label class="form-label">Текстовый редактор:</label>
                    <div id="editor-text" class="editor-container"></div>
                </div>

                <!-- Для задания -->
                <div v-else-if="selectedType === 'task'" class="form-group">
                    <label class="form-label"
                        >Поле для правильного ответа :</label
                    >
                    <textarea
                        v-model="newChapter.correct_answer"
                        class="correct-answer-textarea"
                        placeholder="Введите правильный ответ"
                    ></textarea>
                    <label class="form-label">Задание :</label>
                    <div id="editor-task" class="editor-container"></div>
                </div>

                <!-- Для терминов -->
                <div v-else-if="selectedType === 'terms'" class="form-group">
                    <label class="form-label">Редактор терминов:</label>
                    <div id="editor-terms" class="editor-container"></div>
                </div>
                <!-- Для презентации -->
                <div
                    v-else-if="selectedType === 'presentation'"
                    class="form-group"
                >
                    <label class="form-label"
                        >Конструктор итогового теста:</label
                    >
                    <div id="editor-final-test" class="editor-container"></div>
                    <!-- <div class="form-group">
                    <label class="form-label">Прикрепить файл (PDF/PPTX):</label>
                  </div> -->
                </div>
                <div class="form-group">
                    <label class="form-label">Загрузить файл к лекции</label>
                    <input
                        type="file"
                        accept=".pdf,.ppt,.pptx,video/*,image/*"
                        @change="(e) => (newChapter.file = e.target.files[0])"
                        class="form-input"
                    />
                </div>
                <!-- загрузка презентации -->
                <button type="submit" class="form-button">
                    Добавить главу
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from "vue";
import axios from "axios";
import EditorJS from "@editorjs/editorjs";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import ImageTool from "@editorjs/image";
import QuizTool from "@/components/editorjs-quiz"; // ← ваш плагин

/* ---------------------------------------------------
 *  Утилита: текущий topicId берём из URL
 * ------------------------------------------------- */
function getTopicIdFromUrl() {
    const parts = window.location.pathname.split("/");
    return parts[parts.indexOf("topic") + 1];
}
const topicId = getTopicIdFromUrl();

/* ---------------------------------------------------
 *  Список глав + данные темы
 * ------------------------------------------------- */
const topic = ref({});
const chapters = ref([]);

const currentPageChapters = ref(1);
const pageSizeChapters = ref(6);

const totalPagesChapters = computed(() =>
    Math.ceil(chapters.value.length / pageSizeChapters.value)
);

const paginatedChapters = computed(() => {
    const start = (currentPageChapters.value - 1) * pageSizeChapters.value;
    return chapters.value.slice(start, start + pageSizeChapters.value);
});

watch(chapters, () => {
    currentPageChapters.value = 1;
});

async function loadTopicAndChapters() {
    try {
        const t = await axios.get(`/admin/topic/${topicId}`);
        topic.value = t.data.topic || {};

        const c = await axios.get(`/admin/topic/${topicId}/chapters`);
        chapters.value = c.data.chapters || [];
    } catch (e) {
        console.error(e);
    }
}
onMounted(loadTopicAndChapters);

/* ---------------------------------------------------
 *  --- 1. Редактирование существующей главы (модалка)
 * ------------------------------------------------- */
const editModalOpen = ref(false);
const editingChapter = ref({});
let modalEditorInstance = null;

function openEditModal(chapter) {
    editModalOpen.value = true;
    editingChapter.value = { ...chapter };

    // content приходит строкой – парсим
    if (
        typeof editingChapter.value.content === "string" &&
        editingChapter.value.content.trim()
    ) {
        try {
            editingChapter.value.content = JSON.parse(
                editingChapter.value.content
            );
        } catch {
            editingChapter.value.content = {};
        }
    }
    nextTick(initModalEditor);
}

function closeEditModal() {
    editModalOpen.value = false;
    editingChapter.value = {};
    if (modalEditorInstance) {
        modalEditorInstance.destroy();
        modalEditorInstance = null;
    }
}

function initModalEditor() {
    if (modalEditorInstance) modalEditorInstance.destroy();

    const editorData = editingChapter.value.content || {};

    modalEditorInstance = new EditorJS({
        holder: "editor-edit",
        placeholder: "Добавьте контент главы…",
        data: editorData,
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
            quiz: QuizTool, // чтобы можно было править тесты
        },
    });
}

async function saveEditedChapter() {
    const payload = { ...editingChapter.value };
    payload.points = editingChapter.value.points;
    try {
        if (modalEditorInstance) {
            const data = await modalEditorInstance.save();
            editingChapter.value.content = data;
        }
        const payload = { ...editingChapter.value };
        if (typeof payload.content === "object")
            payload.content = JSON.stringify(payload.content);

        const { data: res } = await axios.put(
            `/api/admin/topic/${topicId}/chapters/${editingChapter.value.id}`,
            payload
        );

        const idx = chapters.value.findIndex((c) => c.id === res.chapter.id);
        if (idx !== -1) chapters.value[idx] = res.chapter;
        closeEditModal();
    } catch (e) {
        console.error(e);
    }
}

async function deleteChapter(id) {
    try {
        await axios.delete(`/api/admin/topic/${topicId}/chapters/${id}`);
        chapters.value = chapters.value.filter((c) => c.id !== id);
    } catch (e) {
        console.error(e);
    }
}

/* ---------------------------------------------------
 * --- 2. Создание новой главы
 * ------------------------------------------------- */
const selectedType = ref("");
const blankChapter = {
    title: "",
    video_url: "",
    content: null,
    correct_answer: "",
    file: null, // общий инпут-файл
    points: 0,
};
const newChapter = ref({ ...blankChapter });

/* Два разных редактора:
 *  - editorInstance  → text / video / task / terms
 *  - quizEditor      → final_test
 */
let editorInstance = null;
let quizEditor = null;

function destroyEditors() {
    if (editorInstance) {
        editorInstance.destroy();
        editorInstance = null;
    }
    if (quizEditor) {
        quizEditor.destroy();
        quizEditor = null;
    }
}

function initEditor(holderId) {
    destroyEditors();
    editorInstance = new EditorJS({
        holder: holderId,
        placeholder: "Добавьте контент главы…",
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

function initQuizEditor() {
    console.log("[initQuizEditor] fired");
    destroyEditors(); // закрываем старые инстансы

    quizEditor = new EditorJS({
        holder: "editor-final-test",
        tools: { quiz: QuizTool },

        /** ← добавляем стартовый блок  */
        data: {
            blocks: [
                {
                    type: "quiz",
                    data: {
                        // минимальные данные
                        questions: [],
                        settings: { shuffle: false },
                    },
                },
            ],
        },
    });
    window.editorFinalTest = quizEditor;
}

/* ---- реагируем на смену типа ---- */
watch(selectedType, async (type) => {
    await nextTick();
    switch (type) {
        case "text":
            initEditor("editor-text");
            break;
        case "task":
            initEditor("editor-task");
            break;
        case "terms":
            initEditor("editor-terms");
            break;
        case "video":
            initEditor("editor-video");
            break;
        case "presentation":
            initQuizEditor();
            break;
        default:
            destroyEditors();
    }
});

/* ---- ручной выбор типа (кнопки/селект) ---- */
function selectType(type) {
    selectedType.value = type;
    newChapter.value = { ...blankChapter };
}

/* ---- отправка формы ---- */
async function submitChapter() {
    try {
        const fd = new FormData();
        fd.append("title", newChapter.value.title);
        fd.append("type", selectedType.value);
        fd.append("correct_answer", newChapter.value.correct_answer);
        /* контент */
        if (selectedType.value === "video" && newChapter.value.video_url) {
            fd.append("video_url", newChapter.value.video_url);
        }
        let contentPayload = {};
        if (selectedType.value === "presentation" && quizEditor) {
            const saved = await quizEditor.save(); // saved = {blocks:[{type:'quiz',…}]}
            contentPayload = saved;
        } else if (editorInstance) {
            contentPayload = await editorInstance.save();
        }
        fd.append("content", JSON.stringify(contentPayload));

        /* файл (если был) */
        if (newChapter.value.file) fd.append("file", newChapter.value.file);
        fd.append("points", newChapter.value.points);
        for (let [key, val] of fd.entries()) {
            console.log(`FormData: ${key} =`, val);
        }
        /* запрос */
        const { data: res } = await axios.post(
            `/admin/topic/${topicId}/chapters`,
            fd
        );
        if (res.chapter) chapters.value.push(res.chapter);

        /* очистка */
        selectedType.value = "";
        newChapter.value = { ...blankChapter };
        destroyEditors();
    } catch (err) {
        console.error("Ошибка при создании главы:", err);
    }
}

/* ---------------------------------------------------
 *  Разное
 * ------------------------------------------------- */
function goBack() {
    window.location.href = document.referrer || "/";
}
</script>

<style scoped>

.form--hidden {
    display: none;
}

h2 {
    margin: 40px 0 40px;
}
.pagination-users {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 30px;
    margin-bottom: 20px;
    font-family: Arial, sans-serif;
}

.pagination-users button {
    min-width: 40px;
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background-color: #f9f9f9;
    color: #333;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.2s, border-color 0.2s, transform 0.1s;
}

.pagination-users button:hover:not(:disabled) {
    background-color: #fff;
    border-color: #888;
    transform: translateY(-1px);
}

.pagination-users button:disabled {
    opacity: 0.5;
    cursor: default;
}

.pagination-users button.active {
    background-color: #698dc9;
    border-color: #698dc9;
    color: #fff;
    font-weight: bold;
}
.form-input--m {
    margin: 0 0 20px;
}
/* Стиль модального окна, пример */
.edit-course-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.form-button--close {
    background: none !important;
    border: 1px solid #007bff !important;
    color: #007bff !important;
}
.modal-buttons {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.modal-overlay {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: flex-start;
    /* Прижимаем окно к верхней границе, если контента много */
    justify-content: center;
    padding: 40px 20px;
    /* Отступы вокруг окна */
    overflow-y: auto;
    /* Скролл при большом контенте */
}

.modal-content {
    background: #fff;
    width: 900px;
    max-width: 90%;
    /* Убираем max-height и overflow-y */
    margin: 40px 0;
    /* Дополнительные отступы сверху/снизу */
    padding: 20px;
    border-radius: 8px;
    position: relative;
}
.editor-container {
    min-height: 150px;
    border: 1px solid #ccc;
    padding: 8px 8px 8px 74px;
}
.btn__user--edit {
    cursor: pointer;
    border: none;
    background: none;
    color: #007bff;
}
.btn__user--delete {
    cursor: pointer;
    background: none;
    border: none;
    color: red;
}
.correct-answer-textarea {
    min-height: 120px; /* Можно отрегулировать под нужный размер */
    padding: 8px;
    margin-top: 5px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    resize: vertical; /* Позволяет вертикально растягивать поле */
    font-size: 14px;
}
.block__info {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 30px 0 40px;
}
.span__sctrelca {
    cursor: pointer;
    user-select: none;
    color: #ffffff;
    background-color: #007bff59;
    padding: 2px 7px;
    border-radius: 25px;
    position: absolute;
    top: 50%;
    left: 25%;
    transform: translateY(-50%);
    display: block;
    font-size: 50px;
    list-style: none;
    text-decoration: none;
}
.form {
    padding: 20px 0;
}
.p__topic {
    text-align: center;
    margin: 0 0 30px;
}
.form-group--margin {
    margin: 0 0 20px;
}
.h3__topic {
    font-size: 30px;
    margin: 40px auto;
    text-align: center;
    padding: 15px;
    background-color: #92c5fc2f;
    border-radius: 15px;
    width: 500px;
}
.chapter-type-selector {
    margin: 20px 0 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.button-group {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.type-button {
    width: 224px;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 15px 55px;
    border-radius: 10px;
    font-size: 17px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.type-button:hover {
    background-color: #0056b3;
    transform: scale(1.05);
}

.type-button:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.4);
}

.chapter-form {
    display: grid;
    grid-template-columns: 1fr;
    width: 700px;
    gap: 20px;
    margin: 0 auto 40px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    font-family: JanoSansProLight;
    font-weight: bold;
    margin-bottom: 8px;
    color: #333;
}
.form-textarea--xl {
    height: 80px;
}
.form-input,
.form-textarea {
    font-family: JanoSansProLight;
    outline: none;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}

.editor-container {
    padding: 10px 10px 10px 74px;
    border: 1px solid #ccc;
    border-radius: 4px;
    min-height: 150px;
    background-color: #fff;
}

.editor-container :deep(.ce-toolbar__content) {
    max-width: none;
}

.editor-container :deep(.ce-toolbar__actions) {
    left: auto !important;
    right: 100% !important;
}

.editor-container :deep(.codex-editor__redactor) {
    min-height: 130px !important;
    margin-right: 0 !important;
}

.form-button {
    width: 700px;
    background-color: #007bff;
    color: #fff;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin: 0 auto;
}

.form-button:hover {
    background-color: #0056b3;
}
table.light-push-table {
    width: 1200px;
    margin: 0 auto;
    border-collapse: collapse;
    background-color: #ffffff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

.light-push-table th,
.light-push-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #e0e0e0;
    text-align: left;
    font-size: 14px;
    white-space: nowrap;
    /* Запрещает перенос текста */
    text-overflow: ellipsis;
    /* Добавляет многоточие при обрезке */
}

.light-push-table th {
    text-align: center;
    background-color: #f0f8ff;
    /* Нежно-голубой цвет */
    font-size: 17px;
    font-weight: 600;
    border-right: 1px solid #d0d0d0;
    padding: 14px;
}

.light-push-table td {
    border-right: 1px solid #f0f0f0;
}

.light-push-table tbody tr:last-child td {
    border-bottom: none;
}

/* Выравнивание номера по центру и фиксированная ширина */
.light-push-table td.number-cell {
    text-align: center;
    font-weight: bold;
    font-size: 15px;
    width: 120px;
    /* Фиксированная ширина для номера */
}

/* Анимация при наведении */
@keyframes rowHover {
    from {
        background-color: #ffffff;
        transform: scale(1);
    }

    to {
        background-color: #e0f7fa;
        transform: scale(1.02);
    }
}

.light-push-table tbody tr:hover {
    animation: rowHover 0.3s ease forwards;
}

/* Убираем рамку справа у последнего столбца */
.light-push-table th:last-child,
.light-push-table td:last-child {
    border-right: none;
}
</style>
