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

                        <!-- Режим редактирования для названия темы -->
                        <td v-if="editingTopicId === topic.id">
                            <input
                                v-model="editingTopic.title"
                                type="text"
                                class="form-input"
                            />
                        </td>
                        <td v-else>
                            {{ topic.title }}
                        </td>

                        <!-- Режим редактирования для описания -->
                        <td v-if="editingTopicId === topic.id">
                            <textarea
                                v-model="editingTopic.description"
                                class="form-textarea"
                            ></textarea>
                        </td>
                        <td v-else>
                            {{ topic.description }}
                        </td>

                        <!-- Ссылка для добавления материала -->
                        <td>
                            <a
                                :href="`/admin/topic/${topic.id}/chapters/create`"
                                class="btn--control"
                                >Добавить главу</a
                            >
                        </td>

                        <!-- Количество материала -->
                        <td>{{ topic.chapters_count }}</td>

                        <!-- Редактирование: если тема редактируется, показываем кнопки "Сохранить" и "Отмена" -->
                        <td
                            class="topic__edit"
                            v-if="editingTopicId === topic.id"
                        >
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

                        <!-- Удаление -->
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
                    <label for="description" class="form-label"
                        >Описание темы:</label
                    >
                    <textarea
                        id="description"
                        v-model="newTopic.description"
                        placeholder="Введите описание темы"
                        class="form-textarea"
                    ></textarea>
                </div>
                <button type="submit" class="form-button">
                    Сохранить тему
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

// 1. Функция для извлечения courseId из URL, например /admin/course/20
function getCourseIdFromUrl() {
    const pathParts = window.location.pathname.split("/");
    const idx = pathParts.indexOf("course");
    return pathParts[idx + 1];
}
const courseId = getCourseIdFromUrl();

// 2. Реактивные переменные
const topics = ref([]); // Список тем
const showTopicForm = ref(false); // Показ/скрытие формы "Добавить тему"
const newTopic = ref({ title: "", description: "" });

// Inline-редактирование
const editingTopicId = ref(null);
const editingTopic = ref({});

// 3. Загрузка тем (GET /admin/course/{course}/topics)
async function loadTopics() {
    try {
        const response = await axios.get(`/admin/course/${courseId}/topics`);
        topics.value = response.data.topics || [];
    } catch (error) {
        console.error("Ошибка при загрузке тем:", error);
    }
}

// 4. Создание новой темы (POST /admin/course/{course}/topics)
async function submitTopic() {
    try {
        const response = await axios.post(
            `/admin/course/${courseId}/topics`,
            newTopic.value
        );
        if (response.data.topic) {
            topics.value.push(response.data.topic);
        }
        newTopic.value = { title: "", description: "" };
        showTopicForm.value = false;
    } catch (error) {
        console.error("Ошибка при создании темы:", error);
    }
}

// 5. Показ/скрытие формы добавления темы
function toggleTopicForm() {
    showTopicForm.value = !showTopicForm.value;
}

// 6. Кнопка "Назад"
function goBack() {
    window.history.back();
}

// 7. Inline-редактирование
function startEditingTopic(topic) {
    editingTopicId.value = topic.id;
    editingTopic.value = { ...topic }; // копируем поля темы
}

async function saveTopic() {
    try {
        // PATCH /admin/topics/{topic}
        const response = await axios.put(
            `/admin/topics/${editingTopic.value.id}`,
            editingTopic.value
        );
        // Находим индекс темы и обновляем
        const index = topics.value.findIndex(
            (t) => t.id === editingTopic.value.id
        );
        if (index !== -1) {
            topics.value[index] = response.data.topic;
        }
        editingTopicId.value = null;
        editingTopic.value = {};
    } catch (error) {
        console.error("Ошибка при обновлении темы:", error);
    }
}

function cancelEditingTopic() {
    editingTopicId.value = null;
    editingTopic.value = {};
}

// 8. Удаление темы (DELETE /admin/topics/{topic})
async function deleteTopic(topicId) {
    if (!confirm("Вы действительно хотите удалить тему?")) return;
    try {
        const response = await axios.delete(`/admin/topics/${topicId}`);
        // Удаляем тему из локального массива
        topics.value = topics.value.filter((t) => t.id !== topicId);
    } catch (error) {
        console.error("Ошибка при удалении темы:", error);
    }
}

// 9. Загрузка тем при монтировании
onMounted(() => {
    loadTopics();
});
</script>

<style scoped>
.btn--control{
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
