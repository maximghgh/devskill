<template>
    <div>
        <div class="block__info">
            <a class="span__sctrelca" href="#" @click.prevent="goBack">🠔</a>
            <h1>Темы курса</h1>
        </div>

        <!-- Таблица с темами -->
        <div v-if="topics.length">
            <table class="light-push-table light-push-table--s">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Название темы</th>
                        <th>Описание</th>
                        <th>Добавить материал</th>
                        <th>Количество материала</th>
                        <th>Изменения</th>
                        <th>Удалить</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(topic, index) in topics" :key="topic.id">
                        <td>{{ index + 1 }}</td>

                        <!-- Inline‑редактирование -->
                        <td v-if="editingTopicId === topic.id">
                            <input
                                v-model="editingTopic.title"
                                class="form-input"
                            />
                        </td>
                        <td v-else>
                            {{ topic.title }}
                        </td>

                        <td v-if="editingTopicId === topic.id">
                            <textarea
                                v-model="editingTopic.description"
                                class="form-textarea"
                            ></textarea>
                        </td>
                        <td v-else>
                            {{ topic.description }}
                        </td>

                        <td>
                            <a
                                :href="`/admin/topic/${topic.id}/chapters/create`"
                                class="btn--control"
                            >
                                Добавить главу
                            </a>
                        </td>

                        <td>{{ topic.chapters_count }}</td>

                        <td v-if="editingTopicId === topic.id">
                            <button class="btn__user--edit" @click="saveTopic">
                                Сохранить
                            </button>
                            <button
                                class="btn__user--edit"
                                @click="cancelEditingTopic"
                            >
                                Отмена
                            </button>
                        </td>
                        <td v-else>
                            <button
                                class="btn__user--edit"
                                @click="startEditingTopic(topic)"
                            >
                                Редактировать
                            </button>
                        </td>

                        <td>
                            <button
                                class="btn__user--delete"
                                @click="deleteTopic(topic.id)"
                            >
                                Удалить
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="center">
            <p>Темы отсутствуют.</p>
        </div>

        <!-- Кнопки управления -->
        <div class="btn_block" style="margin: 1rem 0">
            <button class="type-button" @click="toggleTopicForm">
                {{ showTopicForm ? "Отмена добавления темы" : "Добавить тему" }}
            </button>
            <div class="edit__testfinal">
                <button class="type-button"
                        @click="toggleFinalTestForm"
                        :disabled="showEditTestForm">
                    {{ showFinalTestForm ? "Отмена создания теста" : "Создать итоговый тест" }}
                </button>

                <button class="type-button"
                        @click="onEditTestClick"
                        :disabled="showFinalTestForm">
                    {{ showEditTestForm ? "Отмена редактирования теста" : "Редактировать тест" }}
                </button>
            </div>
        </div>

        <!-- Форма создания темы -->
        <div v-if="showTopicForm" class="topic-form">
            <h2>Новая тема</h2>
            <form @submit.prevent="submitTopic" class="course-form">
                <div class="form-group">
                    <label class="form-label">Название темы:</label>
                    <input
                        v-model="newTopic.title"
                        required
                        class="form-input"
                    />
                </div>
                <div class="form-group">
                    <label class="form-label">Описание темы:</label>
                    <textarea
                        v-model="newTopic.description"
                        class="form-textarea"
                    ></textarea>
                </div>
                <button type="submit" class="form-button">
                    Сохранить тему
                </button>
            </form>
        </div>

        <!-- Форма создания итогового теста -->
        <div v-if="showFinalTestForm" class="course-form">
            <h3>Создать итоговый тест</h3>
            <form @submit.prevent="submitFinalTest">
                <div class="form-group">
                    <div
                        id="editor-final-test-create"
                        class="editor-container"
                    ></div>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="form-button">
                        Сохранить тест
                    </button>
                </div>
            </form>
        </div>
        <div v-if="showEditTestForm" class="course-form">
            <h3>Редактировать итоговый тест</h3>
            <form @submit.prevent="submitEditedTest">
                <div class="form-group">
                <div id="editor-final-test-edit" class="editor-container"></div>
                </div>
                <div class="form-buttons">
                <button type="submit" class="form-button">Сохранить изменения</button>
                <button type="button" class="btn-delete" @click="deleteFinalTest">Удалить тест</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from "vue";
import axios from "axios";
import EditorJS from "@editorjs/editorjs";
import QuizTool from "@/components/editorjs-quiz";
import { globalNotification } from "../../globalNotification";

// ——————————————————————————
// Извлечение ID курса из URL
// ——————————————————————————
function getCourseIdFromUrl() {
    const parts = window.location.pathname.split("/");
    return parts[parts.indexOf("course") + 1];
}
const courseId = getCourseIdFromUrl();

// ——————————————————————————
// Состояние раздела «Темы»
// ——————————————————————————
const topics = ref([]);
const showTopicForm = ref(false);
const newTopic = ref({ title: "", description: "" });
const editingTopicId = ref(null);
const editingTopic = ref({});

// ——————————————————————————
// Состояние раздела «Итоговый тест»
// ——————————————————————————
const showFinalTestForm = ref(false);
const showEditTestForm = ref(false);
const passScore = ref(50);
let quizEditor = null;
let currentHolderId = null;
// ——————————————————————————
// Загрузка списка тем
// ——————————————————————————
async function loadTopics() {
    try {
        const { data } = await axios.get(`/admin/course/${courseId}/topics`);
        topics.value = (data.topics || []).sort(
            (a, b) => new Date(a.created_at) - new Date(b.created_at)
        );
    } catch (e) {
        console.error("Ошибка при загрузке тем:", e);
    }
}
onMounted(loadTopics);

// ——————————————————————————
// Управление темами
// ——————————————————————————
function toggleTopicForm() {
    showTopicForm.value = !showTopicForm.value;
}

async function submitTopic() {
    try {
        const { data } = await axios.post(
            `/admin/course/${courseId}/topics`,
            newTopic.value
        );
        topics.value.push(data.topic);
        newTopic.value = { title: "", description: "" };
        showTopicForm.value = false;
        globalNotification.categoryMessage = "Тема создана";
        globalNotification.type = "success";
    } catch (e) {
        console.error("Ошибка создания темы:", e);
        globalNotification.categoryMessage = "Заполните все поля";
        globalNotification.type = "error";
    }
}

function startEditingTopic(topic) {
    editingTopicId.value = topic.id;
    editingTopic.value = { ...topic };
}

async function saveTopic() {
    try {
        const { data } = await axios.put(
            `/admin/topics/${editingTopic.value.id}`,
            editingTopic.value
        );
        const idx = topics.value.findIndex((t) => t.id === data.topic.id);
        if (idx !== -1) topics.value[idx] = data.topic;
        editingTopicId.value = null;
        globalNotification.categoryMessage = "Тема изменена";
        globalNotification.type = "success";
    } catch (e) {
        console.error("Ошибка обновления темы:", e);
        globalNotification.categoryMessage = "Ошибка обновления";
        globalNotification.type = "error";
    }
}

function cancelEditingTopic() {
    editingTopicId.value = null;
}

async function deleteTopic(id) {
    if (!confirm("Удалить тему?")) return;
    try {
        await axios.delete(`/admin/topics/${id}`);
        topics.value = topics.value.filter((t) => t.id !== id);
        globalNotification.categoryMessage = "Тема удалена";
        globalNotification.type = "success";
    } catch (e) {
        console.error("Ошибка удаления темы:", e);
        globalNotification.categoryMessage = "Ошибка удаления";
        globalNotification.type = "error";
    }
}

// ——————————————————————————
// Инициализация EditorJS
// ——————————————————————————
function initQuizEditor(holderId, initialData = null) {
  if (quizEditor) {
    quizEditor.destroy();
    quizEditor = null;
  }
  currentHolderId = holderId;
  quizEditor = new EditorJS({
    holder: holderId,
    tools: { quiz: QuizTool },
    data: initialData || {
      blocks: [{ type: "quiz", data: { questions: [], settings: { shuffle: false } } }]
    }
  });
}

// ——————————————————————————
// Создание итогового теста
// ——————————————————————————
function toggleFinalTestForm() {
  // если открыт блок редактирования — закрываем
  if (showEditTestForm.value) {
    showEditTestForm.value = false;
  }

  showFinalTestForm.value = !showFinalTestForm.value;

  if (showFinalTestForm.value) {
    nextTick(() => initQuizEditor('editor-final-test-create'));
  } else if (quizEditor) {
    quizEditor.destroy();
    quizEditor = null;
    currentHolderId = null;
  }
}


async function submitFinalTest() {
    try {
        const saved = await quizEditor.save();
        await axios.post(`/api/admin/course/${courseId}/final-test`, {
            questions: saved,
        });
        globalNotification.categoryMessage = "Итоговый тест создан";
        globalNotification.type = "success";
        toggleFinalTestForm();
    } catch (e) {
        console.error("Ошибка создания теста:", e);
        globalNotification.categoryMessage = "Ошибка создания теста";
        globalNotification.type = "error";
    }
}

// ——————————————————————————
// Редактирование итогового теста
// ——————————————————————————
async function onEditTestClick() {
  // если открыт блок создания — закрываем
  if (showFinalTestForm.value) {
    showFinalTestForm.value = false;
  }

  showEditTestForm.value = !showEditTestForm.value;

  if (showEditTestForm.value) {
    try {
      const { data } = await axios.get(`/api/admin/course/${courseId}/final-test/data`);
      passScore.value = data.pass_score;
      await nextTick();
      initQuizEditor('editor-final-test-edit', data.questions);
    } catch (e) {
      console.error("Ошибка при загрузке теста:", e);
      globalNotification.categoryMessage = "Ошибка при загрузке теста";
      globalNotification.type = "error";
      showEditTestForm.value = false;
    }
  } else if (quizEditor) {
    quizEditor.destroy();
    quizEditor = null;
    currentHolderId = null;
  }
}


async function submitEditedTest() {
    try {
        const saved = await quizEditor.save();
        await axios.put(`/api/admin/course/${courseId}/final-test/data`, {
            questions: saved,
        });
        globalNotification.categoryMessage = "Тест обновлён";
        globalNotification.type = "success";
        onEditTestClick();
    } catch (e) {
        console.error("Ошибка при обновлении теста:", e);
        globalNotification.categoryMessage = "Ошибка при обновлении теста";
        globalNotification.type = "error";
    }
}

async function deleteFinalTest() {
    if (!confirm("Удалить итоговый тест?")) return;
    try {
        await axios.delete(`/api/admin/course/${courseId}/final-test/data`);
        globalNotification.categoryMessage = "Тест удалён";
        globalNotification.type = "success";
        onEditTestClick();
    } catch (e) {
        console.error("Ошибка при удалении теста:", e);
        globalNotification.categoryMessage = "Ошибка при удалении теста";
        globalNotification.type = "error";
    }
}

// ——————————————————————————
// Навигация назад
// ——————————————————————————
function goBack() {
    window.location.href = document.referrer || "/admin";
}
</script>

<style scoped>
.btn-delete {
    background: none;
    border: none;
    color: rgb(255, 0, 0);
    font-size: 15px;
    cursor: pointer;
}
.btn-delete:hover {
    color: rgba(255, 0, 0, 0.822);
}
.form-buttons {
    margin: 0 0 30px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}
.test-del {
    text-decoration: none;
}
.edit__testfinal {
    display: flex;
    align-items: center;
    flex-direction: row;
    gap: 20px;
}
.editor-container {
    padding: 10px 10px 10px 74px;
    border: 1px solid #ccc;
    border-radius: 4px;
    min-height: 150px;
    background-color: #fff;
    margin: 10px 0 30px;
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
.btn_block {
    display: flex;
    justify-content: center;
    gap: 20px;
}
.center {
    text-align: center;
}
.btn--control {
    text-decoration: none;
    color: green;
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
.form-textarea {
    width: 700px;
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
.course-form {
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

.form-input,
.form-textarea {
    font-family: JanoSansProLight;
    outline: none;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 16px;
}

.form-textarea {
    resize: none;
    min-height: 80px;
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

.type-button {
    width: 480px;
    display: block;
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 15px 150px;
    border-radius: 10px;
    font-size: 17px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.type-button:hover {
    background-color: #0056b3;
    transform: scale(1.05);
}
table.light-push-table {
    margin: 45px auto;
    width: 1500px;
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
