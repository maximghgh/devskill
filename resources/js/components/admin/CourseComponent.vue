<template>
    <div>
        <div class="block__info">
            <a class="span__sctrelca" href="#" @click.prevent="goBack">🠔</a>
            <h1>Темы курса</h1>
        </div>
        <!-- Таблица с темами -->
        <div v-if="topics.length">
            <table class="light-push-table" v-if="topics.length">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Название темы</th>
                        <th>Описание</th>
                        <th>Добавить материал</th>
                        <th>Количество материала</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(topic, index) in topics" :key="topic.id">
                        <td>{{ index + 1 }}</td>
                        <td>{{ topic.title }}</td>
                        <td>{{ topic.description }}</td>
                        <td>
                            <a :href="`/admin/topic/${topic.id}/chapters/create`">
                            Добавить главу
                            </a>
                        </td>
                        <td>{{ topic.chapters_count }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Сообщение, если тем нет -->
        <div v-else>
            <p>Темы отсутствуют.</p>
        </div>

        <!-- Кнопка для показа/скрытия формы создания темы -->
        <button class="type-button" @click="toggleTopicForm">
            {{ showTopicForm ? "Отмена" : "Добавить тему" }}
        </button>

        <!-- Форма создания темы -->
        <div v-if="showTopicForm" class="topic-form">
            <h2>Новая тема</h2>
            <form @submit.prevent="submitTopic" class="course-form">
                <div class="form-group">
                    <label for="title" class="form-label">Название темы:</label>
                    <input
                        type="text"
                        id="title"
                        v-model="newTopic.title"
                        required
                        placeholder="Введите название темы"
                        class="form-input"
                    />
                </div>
                <div class="form-group">
                    <label for="description" class="form-label">Описание темы:</label>
                    <textarea
                        id="description"
                        v-model="newTopic.description"
                        placeholder="Введите описание темы"
                        class="form-textarea"
                    ></textarea>
                </div>
                <button type="submit" class="form-button">Сохранить тему</button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

// 1. Функция, которая ищет в URL "/admin/course/20"
function getCourseIdFromUrl() {
  const pathParts = window.location.pathname.split('/'); 
  // pathParts будет ["", "admin", "course", "20"] при URL http://127.0.0.1:8000/admin/course/20
  // Найдем индекс 'course'
  const idx = pathParts.indexOf('course');
  // Следующий элемент после 'course' — это и есть "20"
  return pathParts[idx + 1];
}

const courseId = getCourseIdFromUrl();

// 2. Рефы для списка тем, флага формы и новой темы
const topics = ref([]);
const showTopicForm = ref(false);
const newTopic = ref({ title: '', description: '' });

// 3. Загрузка тем
async function loadTopics() {
  try {
    const response = await axios.get(`/admin/course/${courseId}/topics`);
    console.log('Ответ от сервера:', response.data);
    topics.value = response.data.topics || [];
  } catch (error) {
    console.error('Ошибка при загрузке тем:', error);
  }
}

// 4. Создание темы
async function submitTopic() {
  try {
    const response = await axios.post(
      `/admin/course/${courseId}/topics`,
      newTopic.value
    );
    if (response.data.topic) {
      topics.value.push(response.data.topic);
    }
    newTopic.value = { title: '', description: '' };
    showTopicForm.value = false;
  } catch (error) {
    console.error('Ошибка при создании темы:', error);
  }
}

function toggleTopicForm() {
  showTopicForm.value = !showTopicForm.value;
}

// 5. Загружаем темы при монтировании
onMounted(() => {
  loadTopics();
});

function goBack() {
  window.history.back();
}
</script>

<style scoped>
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
    display: block;
    margin: 35px auto;
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
    width: 1200px;
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
