<template>
  <div class="container ">
    <!-- Заголовок курса -->
    <h1 v-if="!loading && !error">
      Итоговый тест: {{ course.title || 'Без названия' }}
    </h1>

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
                  required
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
          Правильно: {{ quizResult.correctCount }} из {{ quizResult.total }}
        </p>
      </div>
    </div>

    <!-- Лоадер / Ошибка -->
    <p v-else-if="loading">Загружаем тест…</p>
    <p v-else-if="error">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import axiosLib from 'axios'

// props
const props = defineProps({ courseId: [Number, String] })

// axios
const axios = inject('$axios', axiosLib)

// state
const loading     = ref(false)
const error       = ref('')
const course      = ref({})    // <-- здесь храним инфо о курсе
const quizData    = ref(null)
const userAnswers = ref({})
const quizResult  = ref(null)

onMounted(async () => {
  if (!props.courseId) {
    error.value = 'Не указан ID курса.'
    return
  }
  loading.value = true

  try {
    const { data } = await axios.get(`/api/final-test/${props.courseId}`)

    // сохраним данные курса
    course.value = data.course

    // найдём quiz-блок в JSON от EditorJS
    const quizBlock = data.test.blocks.find(b => b.type === 'quiz')
    if (!quizBlock) throw new Error('Quiz-блок не найден')

    quizData.value = quizBlock.data

    // инициализация userAnswers
    quizData.value.questions.forEach(q => {
      userAnswers.value[q.id] = q.type === 'multiple' ? [] : null
    })
  } catch (e) {
    console.error(e)
    error.value = 'Не удалось загрузить тест.'
  } finally {
    loading.value = false
  }
})

function submitQuiz() {
  const questions   = quizData.value.questions
  let correctCount  = 0

  questions.forEach(q => {
    const given = userAnswers.value[q.id]
    if (q.type === 'single') {
      if (given === q.answer) correctCount++
    } else {
      const correctArr = Array.isArray(q.answer) ? q.answer : [q.answer]
      const givenArr   = Array.isArray(given) ? [...given].sort() : []
      if (
        givenArr.length === correctArr.length &&
        givenArr.every((v,i) => v === [...correctArr].sort()[i])
      ) {
        correctCount++
      }
    }
  })

  const total = questions.length
  quizResult.value = {
    correctCount,
    total,
    score: Math.round((correctCount / total) * 100),
  }
}
</script>

<style scoped>
.container {
    width: 100%;
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 16px;
}
.container h1 {
    margin: 0 0 30px;
}
.card-title{
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
