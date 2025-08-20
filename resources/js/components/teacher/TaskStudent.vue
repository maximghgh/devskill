<template>
    <div>
        <h2>Список курсов</h2>
        <ol class="course-list">
            <li v-for="course in courses" :key="course.id" class="course-item">
            <a href="" class="course-link">
                {{ course.card_title }}
            </a>
            </li>
        </ol>
    </div>
</template>


<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const courses = ref([]);

// Получение курсов преподавателя
onMounted(async () => {
  try {
    // Извлекаем teacher из localStorage
    const user = JSON.parse(localStorage.getItem('user'));
    const teacherId = user?.id;  // Важно удостовериться, что id существует

    if (!teacherId) {
      console.error('Teacher ID is missing');
      return;
    }

    // Формируем запрос (POST-запрос)
    const response = await axios.post('/api/coursestaks', { teacher_id: teacherId });
    
    // Присваиваем данные курсов
    courses.value = response.data;

  } catch (error) {
    console.error('Ошибка при загрузке курсов:', error);
  }
});
</script>

<style scoped>
.course-list {
    width: 1200px;
    margin: 0 auto;
    padding: 0;
    display: flex;
    flex-direction: column;
}

.course-item {
    margin: 10px 0;
}

.course-link {
    text-decoration: none;
    color: #3498db;
    font-size: 25px;
}

.course-link:hover {
    color: #2980b9;
    border-bottom: 1px solid #2980b9;
}
</style>




