<template>
    <div class="container">
        <!-- Заголовок курса -->
        <div class="backs">
            <button @click="goBack" class="btn-back">
                Вернуться в личный кабинет
            </button>
        </div>
        <h1 v-if="!loading && !error">Итоговый тест</h1>

        <!-- Сам квиз -->
        <div v-if="quizData" class="final-quiz mb-5">
            <form @submit.prevent="submitQuiz">
                <div
                    v-for="q in quizData.questions"
                    :key="q.id"
                    class="card mb-3"
                >
                    <div class="card-body">
                        <h5 class="card-title">{{ q.id }}. {{ q.prompt }}</h5>
                        <div class="quiz-options mt-3">
                            <div
                                v-for="(opt, i) in q.options"
                                :key="i"
                                class="form-check"
                            >
                                <input
                                    v-if="q.type === 'single'"
                                    class="form-check-input quiz-input"
                                    type="radio"
                                    :name="'q' + q.id"
                                    :id="'q' + q.id + '_' + i"
                                    v-model="userAnswers[q.id]"
                                    :value="i"
                                />
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
                      <p v-if="highlightedQuestion === q.id" class="missing-text">
                        Пожалуйста, ответьте на вопрос
                      </p>
                    </div>
                </div>

                <button type="submit" class="button button--sub">
                    Завершить тест
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

        <!-- Лоадер / Ошибка -->
        <p v-else-if="loading">Загружаем тест…</p>
        <p v-else-if="error">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, onMounted, inject } from "vue";
import axiosLib from "axios";

function goBack() {
    window.history.back(); // или history.back();
}

const storedUser = JSON.parse(localStorage.getItem("user") || "{}");
// props
const props = defineProps({ courseId: [Number, String] });

// axios
const axios = inject("$axios", axiosLib);

// state
const loading = ref(false);
const error = ref("");
const course = ref({}); // <-- здесь храним инфо о курсе
const quizData = ref(null);
const userAnswers = ref({});
const quizResult = ref(null);
const highlightedQuestion = ref(null);
const missingMessage = ref("");

onMounted(async () => {
    if (!props.courseId) {
        error.value = "Не указан ID курса.";
        return;
    }
    loading.value = true;

    try {
        const { data } = await axios.get(`/api/final-test/${props.courseId}`, {
            params: { user_id: storedUser.id },
        });
        course.value = data.course || {};
        const root = data.questions ?? data.test;
        if (!root?.blocks) throw new Error("В ответе нет blocks");

        const quizBlock = root.blocks.find((b) => b.type === "quiz");
        if (!quizBlock) throw new Error("Quiz-блок не найден");

        quizData.value = quizBlock.data;

        // инициализация userAnswers
        quizData.value.questions.forEach((q) => {
            userAnswers.value[q.id] = q.type === "multiple" ? [] : null;
        });
    } catch (e) {
        if (
            e.response?.status === 403 &&
            e.response.data?.message === "chapters_incomplete"
        ) {
            error.value = "Сначала пройдите все главы курса.";
        } else {
            error.value = "Не удалось загрузить тест.";
        }
        console.error(e);
        error.value = "Не удалось загрузить тест.";
    } finally {
        loading.value = false;
    }
});

function submitQuiz() {
    // 1) Считаем правильные ответы
    const questions = quizData.value.questions;
    const missing = questions.find(q => {
      const ans = userAnswers.value[q.id];
      return q.type === 'single'
        ? ans == null
        : !Array.isArray(ans) || ans.length === 0;
    });
    if (missing) {
      highlightedQuestion.value = missing.id;
      return;
    }

    highlightedQuestion.value = null;
    let correctCount = 0;

    questions.forEach((q) => {
        const given = userAnswers.value[q.id];
        if (q.type === "single") {
            if (given === q.answer) correctCount++;
        } else {
            const correctArr = Array.isArray(q.answer) ? q.answer : [q.answer];
            const givenArr = Array.isArray(given) ? [...given].sort() : [];
            if (
                givenArr.length === correctArr.length &&
                givenArr.every((v, i) => v === [...correctArr].sort()[i])
            ) {
                correctCount++;
            }
        }
    });

    // 2) Считаем общий балл и процент
    const total = questions.length;
    const score = Math.round((correctCount / total) * 100);

    quizResult.value = {
        correctCount,
        total,
        score,
    };

    // 3) Если меньше 50% — показываем сообщение и выходим
    if (score < 50) {
        alert(
            "Нужно набрать не менее 50% для сохранения результата. Проверьте ответы и попробуйте снова."
        );
        return;
    }

    // 4) Иначе отправляем на бэк и возвращаемся назад
    axios
        .post(`/api/final-test/${props.courseId}/submit`, {
            user_id: storedUser.id,
            answers: userAnswers.value,
            correct_count: correctCount,
            total_questions: total,
            score: score,
        })
        .then(() => {
            // возвращаемся на предыдущую страницу
            window.history.back();
        })
        .catch((err) => {
            console.error("Result save failed", err);
            alert("Ошибка при сохранении результата");
        });
}
</script>

<style scoped>
.missing-text {
    color: #dc3545;
    font-weight: 500;
    margin-bottom: 1rem;
    font-size: 14px;
}
.btn-back {
    font-size: 15px;
    background: none;
    border: none;
    color: #5b4bff;
    cursor: pointer;
    margin-bottom: 20px;
}
.backs {
    display: flex;
    height: 20px;
    width: 100%;
    max-width: 1250px;
    margin: 0 auto;
}
.container {
    width: 100%;
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 16px;
}
.container h1 {
    margin: 0 0 30px;
}
.card-title {
    font-size: 20px;
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
    margin: 0 auto;
    width: 100%;
    max-width: 800px;
    padding: 24px;
    background: #f8f9fa;
    border-radius: 8px;
}
.quiz-options .form-check {
    margin-top: 0.75rem;
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
</style>
