<template>
    <div class="block">
        <div v-if="submissions.length === 0">
            <p>Нет отправленных заданий</p>
        </div>
        <div
            v-for="submission in submissions"
            :key="submission.id"
            class="submission-card"
            :data-status="submission.status"
        >
            <p>
                <b>Тема:</b>
                {{
                    submission.chapter && submission.chapter.title
                        ? submission.chapter.title
                        : `#${submission.chapter_id}`
                }}
            </p>
            <p><b>Комментарий ученика:</b> {{ submission.comment }}</p>
            <p v-if="submission.file_path">
                <b>Файл:</b>
                <a :href="submission.file_url" target="_blank">Скачать</a>
            </p>

            <hr />

            <!-- Показываем сообщение только после успешного сохранения -->
            <div v-if="submissionMessages[submission.id]">
                <p>
                    <b>{{ submissionMessages[submission.id] }}</b>
                </p>
            </div>

            <!-- Форма ответа преподавателя, показываем только если статус не "Зачтено" или "Не зачтено" -->
            <div v-if="submission.showForm !== false">
                <form @submit.prevent="sendReview(submission)">
                    <label>Комментарий:</label><br />
                    <textarea
                        v-model="submission.teacher_comment"
                        rows="3"
                    ></textarea
                    ><br />

                    <label>Статус:</label>
                    <select v-model="submission.status">
                        <option value="in_review">На проверке</option>
                        <option value="rejected">Не зачтено</option>
                        <option value="approved">Зачтено</option></select
                    ><br />

                    <label>Оценка:</label>
                    <input
                        type="number"
                        v-model="submission.grade"
                        min="0"
                        max="100"
                    /><br />

                    <button type="submit">Сохранить</button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

// Инициализируем реактивные данные
const submissionMessages = ref({});
const root = document.getElementById("app");
const courseId = root.dataset.courseId;
const studentId = root.dataset.studentId;

const student = ref(null);
const submissions = ref([]);

onMounted(async () => {
    try {
        const res = await axios.get(
            `/api/course/${courseId}/student/${studentId}/submissions`
        );
        student.value = res.data.student;
        submissions.value = res.data.submissions;

        // Инициализируем свойство showForm для всех заданий в зависимости от статуса
        submissions.value.forEach((submission) => {
            submission.showForm = submission.status === "pending"; // Показываем форму только для 'pending'

            // Добавляем логику для отображения текста в зависимости от статуса или оценки
            if (submission.status === "approved" || submission.grade >= 4) {
                submissionMessages.value[submission.id] =
                    "Задание успешно пройдено!";
            } else if (submission.status === "rejected") {
                submissionMessages.value[submission.id] =
                    "Отправлено на доработку";
            } else {
                submissionMessages.value[submission.id] = ""; // Ожидаем действия преподавателя
            }
        });

        // Логируем данные для отладки
        console.log(submissions.value); // Для отладки
    } catch (e) {
        console.error("Ошибка загрузки:", e);
    }
});

// Отправка ответа учителя
async function sendReview(submission) {
    try {
        // Отправляем запрос на сервер для обновления
        const response = await axios.put(`/api/submissions/${submission.id}`, {
            teacher_comment: submission.teacher_comment,
            status: submission.status,
            grade: submission.grade,
        });

        // Обновляем данные в UI
        const updatedSubmission = response.data.submission;

        // Найдем текущую запись в submissions и обновим её
        const index = submissions.value.findIndex(
            (s) => s.id === updatedSubmission.id
        );
        if (index !== -1) {
            submissions.value[index] = updatedSubmission;
        }

        // Отображаем сообщение после успешного обновления
        if (
            updatedSubmission.status === "approved" ||
            updatedSubmission.grade >= 4
        ) {
            submissionMessages.value[updatedSubmission.id] =
                "Задание успешно пройдено!";
        } else if (
            updatedSubmission.status === "rejected" ||
            updatedSubmission.grade <= 3
        ) {
            submissionMessages.value[updatedSubmission.id] =
                "Отправлено на доработку";
        }

        // Логируем сообщение для отладки
        console.log(submissionMessages.value);

        // Скрываем форму после успешного сохранения
        submissions.value[index].showForm = false;

        alert("Ответ сохранён!");
    } catch (e) {
        console.error("Ошибка при сохранении ответа:", e);
        alert("Ошибка при сохранении");
    }
}
</script>

<style scoped>
/* контейнер списка (необязательно) */
:host,
._root {
    display: block;
}
.block {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
/* карточка попытки */
.submission-card {
    width: 100%;
    max-width: 1250px;
    background: #ffffff;
    border: 1px solid #e5e7eb; /* gray-200 */
    border-left: 4px solid #3c4fff; /* по умолчанию — primary */
    border-radius: 16px;
    padding: 16px 18px;
    margin: 16px 0;
    box-shadow: 0 6px 18px rgba(17, 24, 39, 0.06); /* мягкая тень */
}

/* цветная полоса слева в зависимости от статуса */
.submission-card[data-status="approved"] {
    border-left-color: #10b981;
} /* green-500 */
.submission-card[data-status="rejected"] {
    border-left-color: #ef4444;
} /* red-500 */
.submission-card[data-status="pending"],
.submission-card[data-status="in_review"] {
    border-left-color: #f59e0b;
} /* amber-500 */

/* типографика */
.submission-card p {
    margin: 6px 0;
    color: #374151; /* gray-700 */
    line-height: 1.5;
}
.submission-card b {
    color: #111827; /* gray-900 */
}

/* ссылка на файл */
.submission-card a {
    padding: 0 20px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    text-decoration: none;
    color: #3c4fff;
    border-bottom: 1px dashed rgba(60, 79, 255, 0.35);
}
.submission-card a:hover {
    border-bottom-style: solid;
}

/* разделитель */
.submission-card hr {
    border: 0;
    height: 1px;
    background: #f3f4f6; /* gray-100 */
    margin: 12px 0 16px;
}

/* форма проверки */
.submission-card form {
    display: flex;
    flex-direction: column;
}

/* подписи */
.submission-card label {
    display: inline-block;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}

/* поля формы */
.submission-card textarea,
.submission-card select,
.submission-card input[type="number"] {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    background: #ffffff;
    padding: 10px 12px;
    font-size: 14px;
    color: #111827;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.submission-card textarea {
    min-height: 96px;
    resize: vertical;
}
.submission-card textarea:focus,
.submission-card select:focus,
.submission-card input[type="number"]:focus {
    border-color: #3c4fff;
    box-shadow: 0 0 0 4px rgba(60, 79, 255, 0.12);
}

/* кнопка сохранения */
.submission-card button[type="submit"] {
    justify-self: start;
    appearance: none;
    background: #3c4fff;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 10px 16px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: transform 0.05s ease, opacity 0.15s ease, box-shadow 0.15s ease;
    box-shadow: 0 6px 14px rgba(60, 79, 255, 0.25);
}
.submission-card button[type="submit"]:hover {
    opacity: 0.95;
    box-shadow: 0 8px 18px rgba(60, 79, 255, 0.35);
}
.submission-card button[type="submit"]:active {
    transform: translateY(1px);
}

/* адаптив: немного воздуха на маленьких экранах */
@media (max-width: 640px) {
    .submission-card {
        padding: 14px 14px;
        border-radius: 14px;
    }
}
</style>
