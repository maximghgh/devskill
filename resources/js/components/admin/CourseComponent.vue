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
            <button class="type-button" @click="toggleFinalTestForm">
                {{ showFinalTestForm ? "Отмена создания теста" : "Создать итоговый тест" }}
            </button>
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
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from "vue";
import axios from "axios";
import EditorJS from "@editorjs/editorjs";
import QuizTool from "@/components/editorjs-quiz";
import { globalNotification } from "../../globalNotification";

// --- Извлечение ID из URL ---
function getCourseIdFromUrl() {
  const parts = window.location.pathname.split("/");
  return parts[parts.indexOf("course") + 1];
}
const courseId = getCourseIdFromUrl();

function getTopicIdFromUrl() {
  const parts = window.location.pathname.split("/");
  return parts[parts.indexOf("topic") + 1];
}
const topicId = getTopicIdFromUrl();

// --- Темы ---
const topics = ref([]);
const showTopicForm = ref(false);
const newTopic = ref({ title: "", description: "" });
const editingTopicId = ref(null);
const editingTopic = ref({});

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

function toggleTopicForm() {
  showTopicForm.value = !showTopicForm.value;
}

// Создание темы
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

// Редактирование темы
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
    topics.value[idx] = data.topic;
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

// Удаление темы
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

// --- Итоговый тест ---
const showFinalTestForm = ref(false);
const passScore = ref(50);
let quizEditor = null;

// функция‑переключатель вместо двух open/close
function toggleFinalTestForm() {
  showFinalTestForm.value = !showFinalTestForm.value;

  if (showFinalTestForm.value) {
    // ждем, пока контейнер отрендерится, и запускаем редактор
    nextTick(initQuizEditor);
  } else if (quizEditor) {
    // при закрытии — чистим
    quizEditor.destroy();
    quizEditor = null;
  }
}

function initQuizEditor() {
  if (quizEditor) quizEditor.destroy();
  quizEditor = new EditorJS({
    holder: "editor-final-test-create",
    tools: { quiz: QuizTool },
    data: {
      blocks: [
        {
          type: "quiz",
          data: { questions: [], settings: { shuffle: false } },
        },
      ],
    },
  });
}

async function submitFinalTest() {
  try {
    const saved = await quizEditor.save();
    await axios.post(`/api/admin/course/${courseId}/final-test`, {
      questions: saved,
      pass_score: passScore.value,
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

// --- Навигация назад и загрузка тем ---
function goBack() {
  window.location.href = document.referrer || "/admin";
}

onMounted(loadTopics);
</script>


<style scoped>
.editor-container {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    min-height: 150px;
    background-color: #fff;
    margin: 10px 0 30px;
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
