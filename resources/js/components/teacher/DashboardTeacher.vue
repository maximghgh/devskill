<template>
  <div class="center">
    <h2>Результаты тестирования пользователей</h2>
    <table v-if="rows.length">
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
        <tr v-for="r in rows" :key="r.id">
          <td>{{ r.id }}</td>
          <td>{{ r.user?.name || r.user_id }}</td>
          <td>{{ r.course?.title || r.course?.card_title || r.course_id }}</td>
          <td>{{ r.score }}%</td>
          <td>
            <button @click="openModal(r.id)">Показать ответы</button>
          </td>
        </tr>
      </tbody>
    </table>
    <p v-else>Нет данных для отображения.</p>

    <!-- МОДАЛКА -->
    <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
      <div class="modal">
        <h3>Ответы пользователя: {{ modalData.user }}</h3>
        <ul>
          <li v-for="q in modalData.questions" :key="q.id">
            <strong>{{ q.id }}. {{ q.prompt }}</strong>
            <p>
              <em>Отвечено:</em>
              <span class="question" v-if="q.type==='single'">
                {{ q.options[ modalData.answers[q.id] ] }}
              </span>
              <span v-else>
                {{ (modalData.answers[q.id]||[]).map(i => q.options[i]).join(', ') }}
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
import { ref, onMounted } from 'vue'
import axios from 'axios'

const rows = ref([])
const showModal = ref(false)
const modalData = ref({
  user: '',
  questions: [],   // из final_tests.questions
  answers: {},     // из final_test_results.answers
})

// 1) грузим общий список
onMounted(async () => {
  try {
    const { data } = await axios.get('/api/final-test-results')
    console.log('API returned:', data)
    rows.value = data
  } catch (e) {
    console.error('Ошибка при загрузке результатов:', e)
  }
})


// 2) при клике по кнопке — грузим детали
async function openModal(resultId) {
  try {
    const { data } = await axios.get(`/api/final-test-results/${resultId}`)
    const quizBlock = data.questions.blocks.find(b => b.type === 'quiz')
    const qs = quizBlock?.data?.questions || []
    modalData.value = {
        user: data.user, 
        questions: qs, 
        answers: data.answers, 
    }
    console.log('Detail API for', resultId, data)
    showModal.value = true
  } catch (e) {
    console.error('Не удалось загрузить подробности:', e)
  }
}

function closeModal() {
  showModal.value = false
}
</script>

<style scoped>
/* Оформление таблицы */
table {
  width: 100%;
  max-width: 1200px;
  margin: 20px auto;
  border-collapse: collapse;
  font-family: Arial, sans-serif;
  background-color: #ffffff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  border-radius: 8px;
  overflow: hidden;
}

th, td {
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

.center{
    display: flex;
    align-items: center;
    flex-direction: column;

}
.modal-backdrop {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000;
}
.modal {
  background: #fff; padding: 20px; border-radius: 8px;
  max-height: 80vh; overflow-y: auto; width: 600px;
}
.modal h3 { padding: 0px 0 6px; margin: 0 0 15px;  border-bottom: 1px solid #000000;}
.modal ul { list-style: none; padding: 0; }
.modal li { margin-bottom: 1em; }
.question{ margin-left: 12px;}
</style>
