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
                <th>Изменения</th> <!-- Колонка для редактирования -->
                <th>Удалить</th>   <!-- Колонка для удаления -->
              </tr>
            </thead>
            <tbody>
              <tr v-for="(chapter, index) in chapters" :key="chapter.id">
                <td>{{ index + 1 }}</td>

                <!-- Название -->
                <td>
                  <!-- Если редактируем именно эту главу, показываем input -->
                  <div v-if="editingChapterId === chapter.id">
                    <input v-model="editingChapter.title" class="input__user--edit" />
                  </div>
                  <!-- Иначе просто текст. По клику — режим редактирования -->
                  <div
                    v-else
                    @click="startEditingChapter(chapter)"
                    style="cursor: pointer;"
                  >
                    {{ chapter.title }}
                  </div>
                </td>

                <!-- Тип -->
                <td>
                  <!-- Если редактируем именно эту главу, показываем select -->
                  <div v-if="editingChapterId === chapter.id">
                    <select v-model="editingChapter.type" class="input__user--edit">
                      <option value="video">Видео</option>
                      <option value="text">Текст</option>
                      <option value="task">Задания</option>
                      <option value="terms">Термины</option>
                    </select>
                  </div>
                  <!-- Иначе просто текст. По клику — режим редактирования -->
                  <div
                    v-else
                    @click="startEditingChapter(chapter)"
                    style="cursor: pointer;"
                  >
                    {{ chapter.type }}
                  </div>
                </td>

                <!-- Редактирование: кнопки «Сохранить»/«Отмена» или «Редактировать» -->
                <td>
                  <div v-if="editingChapterId === chapter.id">
                    <button @click="saveChapter" class="btn__user--edit">Сохранить</button>
                    <button @click="cancelEditingChapter" class="btn__user--edit">Отмена</button>
                  </div>
                  <div v-else>
                    <button @click="startEditingChapter(chapter)" class="btn__user--edit">
                      Редактировать
                    </button>
                  </div>
                </td>

                <!-- Удаление -->
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

        <!-- Блок для добавления новой главы -->
        <h2>Добавить новую главу</h2>

        <!-- Выбор типа главы -->
        <div class="chapter-type-selector">
            <p class="form-label">Выберите тип главы:</p>
            <div class="button-group">
                <button @click="selectType('video')" class="type-button">
                    Видео
                </button>
                <button @click="selectType('text')" class="type-button">
                    Текст
                </button>
                <button @click="selectType('task')" class="type-button">
                    Задания
                </button>
                <button @click="selectType('terms')" class="type-button">
                    Термины
                </button>
            </div>
        </div>

        <!-- Форма, которая появляется после выбора типа -->
        <div v-if="selectedType" class="form">
            <h3 class="h3__topic">Добавление главы типа «{{ selectedType }}»</h3>
            <form @submit.prevent="submitChapter" class="chapter-form">
                <!-- Общее поле для названия главы -->
                <div class="form-group">
                    <label class="form-label">Название главы:</label>
                    <input
                        placeholder="Название главы"
                        type="text"
                        v-model="newChapter.title"
                        required
                        class="form-input"
                    />
                </div>

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
                        <label class="form-label">Видео редактор:</label>
                        <div id="editor-video" class="editor-container"></div>
                    </div>
                </div>

                <!-- Для текстового материала -->
                <div v-else-if="selectedType === 'text'" class="form-group">
                    <label class="form-label">Текстовый редактор:</label>
                    <div id="editor-text" class="editor-container"></div>
                </div>

                <!-- Для теста -->
                <div v-else-if="selectedType === 'task'" class="form-group">
                    <label class="form-label">Поле для правильного ответа:</label>
                    <textarea v-model="newChapter.correct_answer" class="correct-answer-textarea" placeholder="Введите правильный ответ"></textarea>
                    <label class="form-label">Редактор теста:</label>
                    <div id="editor-task" class="editor-container"></div>
                </div>

                <!-- Для терминов -->
                <div v-else-if="selectedType === 'terms'" class="form-group">
                    <label class="form-label">Редактор терминов:</label>
                    <div id="editor-terms" class="editor-container"></div>
                </div>

                <button type="submit" class="form-button">
                    Добавить главу
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from "vue";
import axios from "axios";
import EditorJS from "@editorjs/editorjs";
import Header from "@editorjs/header";
import List from "@editorjs/list";
import ImageTool from "@editorjs/image";

const editingChapterId = ref(null);
const editingChapter = ref({});

// Запускаем режим редактирования
function startEditingChapter(chapter) {
  editingChapterId.value = chapter.id;
  // Копируем данные, чтобы не изменять сразу оригинал
  editingChapter.value = { ...chapter };
}

// Сохраняем изменения главы
async function saveChapter() {
  try {
    // PUT-запрос для обновления
    const response = await axios.put(
      `/api/admin/topic/${topicId}/chapters/${editingChapterId.value}`,
      editingChapter.value
    );
    const updatedChapter = response.data.chapter;
    // Обновляем главу в списке
    const index = chapters.value.findIndex(ch => ch.id === editingChapterId.value);
    if (index !== -1) {
      chapters.value[index] = updatedChapter;
    }
    editingChapterId.value = null;
    editingChapter.value = {};
  } catch (error) {
    console.error("Ошибка при обновлении главы:", error);
  }
}

// Отмена редактирования
function cancelEditingChapter() {
  editingChapterId.value = null;
  editingChapter.value = {};
}

// Удаление главы
async function deleteChapter(chapterId) {
  try {
    await axios.delete(`/api/admin/topic/${topicId}/chapters/${chapterId}`);
    chapters.value = chapters.value.filter(ch => ch.id !== chapterId);
  } catch (error) {
    console.error("Ошибка при удалении главы:", error);
  }
}
/**
 * Извлекаем ID темы из URL: /admin/topic/3/chapters/create
 */
function getTopicIdFromUrl() {
  const pathParts = window.location.pathname.split("/");
  const idx = pathParts.indexOf("topic");
  return pathParts[idx + 1];
}

const topicId = getTopicIdFromUrl();

// Данные о теме и списке глав
const topic = ref({});
const chapters = ref([]);

// Выбранный тип главы (video, text, task, terms)
const selectedType = ref("");

// Данные новой главы
const newChapter = ref({
  title: "", // <-- поле для названия главы
  video_url: "", // <-- поле для url видео
  content: null, // <-- поле для editor.js
  correct_answer: "" // <-- поле для правильного ответа
});

// Экземпляр Editor.js
let editorInstance = null;

/**
 * Инициализирует Editor.js в контейнере containerId
 */
function initEditor(containerId) {
  if (editorInstance) {
    editorInstance.destroy();
    editorInstance = null;
  }
  editorInstance = new EditorJS({
    holder: containerId,
    placeholder: "Добавьте контент главы...",
    tools: {
      header: { class: Header, inlineToolbar: ["link"] },
      list: { class: List, inlineToolbar: true },
      image: {
        class: ImageTool,
        config: {
          endpoints: {
            byFile: "/api/uploadFile", // настройте под ваш backend
            byUrl: "/api/fetchUrl",
          },
        },
      },
    },
    onReady: () => {
      console.log("Editor.js готов в контейнере", containerId);
    },
  });
}

/**
 * Следим за сменой selectedType.
 * Как только пользователь выбирает тип, ждём nextTick()
 * и инициализируем редактор в нужном контейнере.
 */
watch(selectedType, async (newType) => {
  if (!newType) return;
  await nextTick();

  if (newType === "text") {
    initEditor("editor-text");
  } else if (newType === "task") {
    initEditor("editor-task");
  } else if (newType === "terms") {
    initEditor("editor-terms");
  } else if (newType === "video") {
    initEditor("editor-video");
  }
});

/**
 * Загрузка темы и списка глав при монтировании
 */
async function loadTopicAndChapters() {
  try {
    // GET /admin/topic/{topicId}
    const topicResponse = await axios.get(`/admin/topic/${topicId}`);
    topic.value = topicResponse.data.topic || {};

    // GET /admin/topic/{topicId}/chapters
    const chaptersResponse = await axios.get(`/admin/topic/${topicId}/chapters`);
    chapters.value = chaptersResponse.data.chapters || [];
  } catch (error) {
    console.error("Ошибка:", error);
  }
}

/**
 * Пользователь выбирает тип главы
 * Сбрасываем поля формы
 */
function selectType(type) {
  selectedType.value = type;
  newChapter.value = {
    title: "",
    video_url: "",
    content: null,
    correct_answer: ""
  };
}

/**
 * Отправка формы для создания новой главы
 */
async function submitChapter() {
  try {
    let editorData = null;
    // Если выбран тип, требующий Editor.js
    if (["text", "task", "terms", "video"].includes(selectedType.value) && editorInstance) {
      editorData = await editorInstance.save();
    }

    // Записываем данные редактора в поле content
    newChapter.value.content = editorData;

    const payload = {
      ...newChapter.value,
      type: selectedType.value, // 'task' вместо 'quiz'
    };

    // POST /admin/topic/{topicId}/chapters
    const response = await axios.post(`/admin/topic/${topicId}/chapters`, payload);

    if (response.data.chapter) {
      chapters.value.push(response.data.chapter);
    }

    // Сбрасываем форму
    selectedType.value = "";
    newChapter.value = {
      title: "",
      video_url: "",
      content: null,
      correct_answer: ""
    };
    if (editorInstance) {
      editorInstance.destroy();
      editorInstance = null;
    }
  } catch (error) {
    console.error("Ошибка при создании главы:", error);
  }
}

onMounted(() => {
  loadTopicAndChapters();
});

/** Кнопка "Назад" */
function goBack() {
  window.history.back();
}
</script>

<style scoped>
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
  min-height: 120px;   /* Можно отрегулировать под нужный размер */
  padding: 8px;
  margin-top: 5px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 4px;
  resize: vertical;    /* Позволяет вертикально растягивать поле */
  font-size: 14px;
}
.block__info{
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 30px 0 40px;
}
.span__sctrelca{
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
.form{
    padding: 20px 0;
}
.p__topic{
    text-align: center;
    margin: 0 0 30px;
}
.form-group--margin{
    margin: 0 0 20px;
}
.h3__topic{
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
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    min-height: 150px;
    background-color: #fff;
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
    margin: 0 auto 60px;
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
