<template>
    <div class="center">
        <div class="menu">
            <ul class="menu__teacher">
                <li class="menu__link">
                    <a href="/taskstudent" class="aside__link">Проверка работ учеников</a>
                </li>
                <li class="menu__link">
                    <a href="/taskstudent" class="aside__link aside__link--support">Техническая поддержка</a>
                </li>
            </ul>
        </div>
        <h2>Результаты тестирования пользователей</h2>
        <!-- Поисковая строка -->
        <div class="search">
            <label for="">Поиск пользователей</label>
            <input
                v-model="searchQuery"
                @focus="showHints = true"
                @blur="onBlur"
                type="text"
                placeholder="Поиск по имени пользователя"
                class="search-input"
            />

            <!-- Сами подсказки -->
            <ul v-if="showHints && hints.length" class="hints-list">
                <li
                    v-for="name in hints"
                    :key="name"
                    @mousedown.prevent="selectHint(name)"
                >
                    {{ name }}
                </li>
            </ul>
        </div>
        <table v-if="filteredRows.length">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Пользователь</th>
                    <th>Курс</th>
                    <th>Правильные ответы(в %)</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="r in filteredRows" :key="r.id">
                    <td>{{ r.id }}</td>
                    <td>{{ r.user?.name || r.user_id }}</td>
                    <td>
                        {{
                            r.course?.title ||
                            r.course?.card_title ||
                            r.course_id
                        }}
                    </td>
                    <td>{{ r.score }}%</td>
                    <td>
                        <button @click="openModal(r.id)">
                            Показать ответы
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
        <p v-else>Пользователь не найден</p>

        <!-- МОДАЛКА -->
        <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
            <div class="modal">
                <h3>Ответы пользователя: {{ modalData.user }}</h3>
                <ul>
                    <li v-for="q in modalData.questions" :key="q.id">
                        <strong class="modal__strong"
                            >{{ q.id }}. {{ q.prompt }}</strong
                        >
                        <p>
                            <em>Отвечено:</em>
                            <span class="question" v-if="q.type === 'single'">
                                {{ q.options[modalData.answers[q.id]] }}
                            </span>
                            <span class="question" v-else>
                                {{
                                    (modalData.answers[q.id] || [])
                                        .map((i) => q.options[i])
                                        .join(", ")
                                }}
                            </span>
                        </p>
                    </li>
                </ul>
                <button @click="closeModal()">Закрыть</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";

const rows = ref([]);
const searchQuery = ref("");
const showHints = ref(false);
const showModal = ref(false);
const modalData = ref({ user: "", questions: [], answers: {} });

onMounted(async () => {
    const { data } = await axios.get("/api/final-test-results");
    rows.value = data;
});

// Для подсказок: достаём уникальные имена
const allNames = computed(() =>
    Array.from(
        new Set(
            rows.value
                .map((r) => r.user?.name)
                .filter((n) => typeof n === "string")
        )
    )
);

// Вычисляем подсказки: начинаются с searchQuery, максимум 5 штук
const hints = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return [];
    return allNames.value
        .filter((name) => name.toLowerCase().startsWith(q))
        .slice(0, 5);
});

// После выбора подсказки подставляем её и скрываем hint-лист
function selectHint(name) {
    searchQuery.value = name;
    showHints.value = false;
}

// Скрываем подсказки с небольшой задержкой,
// чтобы клик по пункту успел обработаться
function onBlur() {
    setTimeout(() => {
        showHints.value = false;
    }, 100);
}

// Фильтрация таблицы
const filteredRows = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return rows.value;
    return rows.value.filter((r) => {
        const name = (r.user?.name || "").toLowerCase();
        const idStr = String(r.user_id);
        return name.includes(q) || idStr.includes(q);
    });
});

// Открыть модалку с деталями
async function openModal(resultId) {
    try {
        const { data } = await axios.get(`/api/final-test-results/${resultId}`);
        const quizBlock = data.questions.blocks.find((b) => b.type === "quiz");
        const qs = quizBlock?.data?.questions || [];
        modalData.value = {
            user: data.user,
            questions: qs,
            answers: data.answers,
        };
        showModal.value = true;
    } catch (e) {
        console.error("Не удалось загрузить подробности:", e);
    }
}

function closeModal() {
    showModal.value = false;
}
</script>

<style scoped>
.menu__teacher{
    width: 100%;
    display: flex;
    justify-content: flex-end;
    gap: 20px;
}
.menu{
    display: flex;
    margin: 60px 0;
}

.aside__link {
    color: #575adf;
    text-decoration: none;
    transition: 0.5s;
}
.aside__link--support{
    background: #575adf;
    border-radius: 15px;
    padding: 5px 10px;
    color: #ffffff;
}
.aside__link--active {
    color: #000;
    text-decoration: underline;
}

.aside__link:hover {
    transition: 0.5s;
    text-decoration: underline;
}
.form-buttons {
    margin: 0 0 30px;
    display: flex;
    flex-direction: column;
    gap: 30px;
}
.modal__strong {
    margin-bottom: 10px;
}
.hints-list {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #ddd;
    border-top: none;
    max-height: 200px;
    overflow-y: auto;
    z-index: 10;
}
.hints-list li {
    padding: 8px 12px;
    cursor: pointer;
}
.hints-list li:hover {
    background-color: #f0f0f0;
}
/* Оформление таблицы */
.search {
    width: 100%;
    max-width: 500px;
    display: flex;
    flex-direction: column;
}
.search-input {
    width: 100%;
    max-width: 500px;
    margin: 10px 0;
    padding: 8px 12px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    display: block;
}
table {
    width: 100%;
    max-width: 1200px;
    margin: 20px auto;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
    background-color: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

th,
td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

thead th {
    background-color: #f5f5f5;
    font-weight: 600;
}

tbody tr:nth-child(even) {
    background-color: #fafafa;
}

tbody tr:hover {
    background-color: #f0f8ff;
}

/* Оформление кнопки */
button {
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: background-color 0.2s ease-in-out, transform 0.1s ease-in-out;
}

button:hover {
    background-color: #0056b3;
    transform: translateY(-1px);
}

button:active {
    transform: translateY(0);
}

.center {
    width: 100%;
    max-width: 1200px;
    display: flex;
    flex-direction: column;
    margin: 0 auto;
}
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    max-height: 80vh;
    overflow-y: auto;
    width: 600px;
}
.modal h3 {
    padding: 0px 0 6px;
    margin: 0 0 15px;
    border-bottom: 1px solid #000000;
}
.modal ul {
    list-style: none;
    padding: 0;
}
.modal li {
    margin-bottom: 1em;
}
.question {
    margin-left: 12px;
}
</style>
